<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../repository/RoleRepository.php';
require_once __DIR__ . '/../attributes/HttpMethod.php';


class AdminController extends AppController
{
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = UserRepository::getInstance();
        $this->roleRepository = RoleRepository::getInstance();
    }

    private function requireAdmin(): void
    {
        $this->requireLogin();
        
        if (!AuthMiddleware::isAdmin()) {
            $this->redirect('/dashboard');
            exit();
        }
    }


    #[HttpMethod(['GET'])]
    public function index(): void
    {
        $this->requireAdmin();
        
        $users = $this->userRepository->getAllUsers();
        $roles = $this->roleRepository->getAllRoles();
        $success = $this->getFlash('success');
        $error = $this->getFlash('error');
        
        $this->render('admin', [
            'users' => $users,
            'roles' => $roles,
            'success' => $success,
            'error' => $error
        ]);
    }


    #[HttpMethod(['GET', 'POST'])]
    public function addUser(): void
    {
        $this->requireAdmin();

        $roles = $this->roleRepository->getAllRoles();

        if ($this->isGet()) {
            $error = $this->getFlash('error');
            $this->render('admin-add-user', array_merge(['roles' => $roles], $error ? ['error' => $error] : []));
            return;
        }

        if (!$this->validateCsrf()) {
            $this->redirectWithError('/admin/add-user', 'Nieprawidłowe żądanie.');
            return;
        }

        $email = $this->sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $name = $this->sanitize($_POST['name'] ?? '');
        $surname = $this->sanitize($_POST['surname'] ?? '');
        $roleId = (int) ($_POST['role_id'] ?? RoleRepository::ROLE_USER);

        if (empty($email) || empty($password) || empty($name) || empty($surname)) {
            $this->redirectWithError('/admin/add-user', 'Wypełnij wszystkie pola');
            return;
        }

        if (!$this->isValidEmail($email)) {
            $this->redirectWithError('/admin/add-user', 'Nieprawidłowy format email');
            return;
        }

        if (strlen($email) > 255 || strlen($name) > 100 || strlen($surname) > 100) {
            $this->redirectWithError('/admin/add-user', 'Dane wejściowe są za długie');
            return;
        }

        if (strlen($password) < 8) {
            $this->redirectWithError('/admin/add-user', 'Hasło musi mieć minimum 8 znaków');
            return;
        }

        if ($this->userRepository->emailExists($email)) {
            $this->redirectWithError('/admin/add-user', 'Użytkownik z tym emailem już istnieje');
            return;
        }

        $validRoleIds = array_column($roles, 'id');
        if (!in_array($roleId, $validRoleIds)) {
            $roleId = RoleRepository::ROLE_USER;
        }

        try {
            $result = $this->userRepository->createUserWithRole($email, $password, $name, $surname, $roleId);
            
            if ($result) {
                $_SESSION['success'] = 'Użytkownik został utworzony pomyślnie';
                $this->redirect('/admin');
            } else {
                $this->redirectWithError('/admin/add-user', 'Błąd podczas tworzenia użytkownika');
            }
        } catch (Exception $e) {
            error_log("Admin create user error: " . $e->getMessage());
            $this->redirectWithError('/admin/add-user', 'Wystąpił błąd. Spróbuj ponownie.');
        }
    }


    #[HttpMethod(['POST'])]
    public function blockUser(): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowe żądanie'], 400);
            return;
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $block = isset($_POST['block']) && $_POST['block'] === '1';

        if ($userId <= 0) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowy ID użytkownika'], 400);
            return;
        }

        if ($userId === $this->getUserId()) {
            $this->json(['success' => false, 'error' => 'Nie możesz zablokować samego siebie'], 400);
            return;
        }

        try {
            $result = $this->userRepository->setUserBlocked($userId, $block);
            
            if ($result) {
                $this->json(['success' => true, 'message' => $block ? 'Użytkownik zablokowany' : 'Użytkownik odblokowany']);
            } else {
                $this->json(['success' => false, 'error' => 'Nie udało się zmienić statusu'], 500);
            }
        } catch (Exception $e) {
            error_log("Admin block user error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Wystąpił błąd'], 500);
        }
    }

    #[HttpMethod(['POST'])]
    public function deleteUser(): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowe żądanie'], 400);
            return;
        }

        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowy ID użytkownika'], 400);
            return;
        }

        if ($userId === $this->getUserId()) {
            $this->json(['success' => false, 'error' => 'Nie możesz usunąć samego siebie'], 400);
            return;
        }

        try {
            $result = $this->userRepository->deleteUser($userId);
            
            if ($result) {
                $this->json(['success' => true, 'message' => 'Użytkownik został usunięty']);
            } else {
                $this->json(['success' => false, 'error' => 'Nie udało się usunąć użytkownika'], 500);
            }
        } catch (Exception $e) {
            error_log("Admin delete user error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Wystąpił błąd'], 500);
        }
    }


    #[HttpMethod(['POST'])]
    public function toggleAdmin(): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowe żądanie'], 400);
            return;
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $isAdmin = isset($_POST['is_admin']) && $_POST['is_admin'] === '1';

        if ($userId <= 0) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowy ID użytkownika'], 400);
            return;
        }

        if ($userId === $this->getUserId() && !$isAdmin) {
            $this->json(['success' => false, 'error' => 'Nie możesz odebrać sobie uprawnień admina'], 400);
            return;
        }

        try {
            $result = $this->userRepository->setUserAdmin($userId, $isAdmin);
            
            if ($result) {
                $this->json(['success' => true, 'message' => $isAdmin ? 'Nadano uprawnienia admina' : 'Odebrano uprawnienia admina']);
            } else {
                $this->json(['success' => false, 'error' => 'Nie udało się zmienić statusu'], 500);
            }
        } catch (Exception $e) {
            error_log("Admin toggle admin error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Wystąpił błąd'], 500);
        }
    }


    #[HttpMethod(['POST'])]
    public function setRole(): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowe żądanie'], 400);
            return;
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $roleId = (int) ($_POST['role_id'] ?? 0);

        if ($userId <= 0) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowy ID użytkownika'], 400);
            return;
        }

        if ($roleId <= 0) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowa rola'], 400);
            return;
        }

        if ($userId === $this->getUserId() && $roleId !== RoleRepository::ROLE_ADMIN) {
            $this->json(['success' => false, 'error' => 'Nie możesz zmienić swojej roli'], 400);
            return;
        }

        $role = $this->roleRepository->getRoleById($roleId);
        if (!$role) {
            $this->json(['success' => false, 'error' => 'Wybrana rola nie istnieje'], 400);
            return;
        }

        try {
            $result = $this->userRepository->setUserRole($userId, $roleId);
            
            if ($result) {
                $this->json(['success' => true, 'message' => 'Zmieniono rolę na: ' . $role['display_name']]);
            } else {
                $this->json(['success' => false, 'error' => 'Nie udało się zmienić roli'], 500);
            }
        } catch (Exception $e) {
            error_log("Admin set role error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Wystąpił błąd'], 500);
        }
    }


    #[HttpMethod(['GET'])]
    public function userPermissions(): void
    {
        $this->requireAdmin();

        $userId = (int) ($_GET['id'] ?? 0);

        if ($userId <= 0) {
            $this->redirect('/admin');
            return;
        }

        $user = $this->userRepository->getUserById($userId);
        if (!$user) {
            $_SESSION['error'] = 'Użytkownik nie istnieje';
            $this->redirect('/admin');
            return;
        }

        $permissions = $this->userRepository->getEffectivePermissions($userId);

        $this->render('admin-user-permissions', [
            'targetUser' => $user,
            'permissions' => $permissions
        ]);
    }


    #[HttpMethod(['POST'])]
    public function savePermissions(): void
    {
        $this->requireAdmin();

        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowe żądanie'], 400);
            return;
        }

        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            $this->json(['success' => false, 'error' => 'Nieprawidłowy ID użytkownika'], 400);
            return;
        }

        $user = $this->userRepository->getUserById($userId);
        if (!$user) {
            $this->json(['success' => false, 'error' => 'Użytkownik nie istnieje'], 400);
            return;
        }

        $permissionsData = $_POST['permissions'] ?? [];

        try {
            foreach ($permissionsData as $permissionId => $value) {
                $permissionId = (int) $permissionId;
                if ($permissionId <= 0) continue;

                if ($value === 'default') {
                    $this->userRepository->removeUserPermission($userId, $permissionId);
                } else {
                    $granted = $value === '1';
                    $this->userRepository->setUserPermission($userId, $permissionId, $granted);
                }
            }

            $this->json(['success' => true, 'message' => 'Uprawnienia zostały zapisane']);
        } catch (Exception $e) {
            error_log("Admin save permissions error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Wystąpił błąd'], 500);
        }
    }
}
