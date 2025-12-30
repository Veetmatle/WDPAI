<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../attributes/HttpMethod.php';


class AdminController extends AppController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = UserRepository::getInstance();
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
        $success = $this->getFlash('success');
        $error = $this->getFlash('error');
        
        $this->render('admin', [
            'users' => $users,
            'success' => $success,
            'error' => $error
        ]);
    }


    #[HttpMethod(['GET', 'POST'])]
    public function addUser(): void
    {
        $this->requireAdmin();

        if ($this->isGet()) {
            $error = $this->getFlash('error');
            $this->render('admin-add-user', $error ? ['error' => $error] : []);
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
        $isAdmin = isset($_POST['is_admin']) && $_POST['is_admin'] === '1';

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

        try {
            $result = $this->userRepository->createUserWithAdmin($email, $password, $name, $surname, $isAdmin);
            
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
}
