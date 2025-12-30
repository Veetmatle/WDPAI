<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../attributes/HttpMethod.php';

class SecurityController extends AppController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = UserRepository::getInstance();
    }

    #[HttpMethod(['GET', 'POST'])]
    public function login(): void
    {
        if ($this->isGet()) {
            $error = $this->getFlash('error');
            $this->render('login', $error ? ['error' => $error] : []);
            return;
        }

        if (!$this->validateCsrf()) {
            $this->redirectWithError('/login', 'Nieprawidłowe żądanie. Odśwież stronę i spróbuj ponownie.');
            return;
        }

        $email = $this->sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->redirectWithError('/login', 'Wypełnij wszystkie pola');
            return;
        }

        if (!$this->isValidEmail($email)) {
            $this->redirectWithError('/login', 'Nieprawidłowy format email');
            return;
        }

        if (strlen($email) > 255 || strlen($password) > 255) {
            $this->redirectWithError('/login', 'Nieprawidłowe dane wejściowe');
            return;
        }

        $user = $this->userRepository->getUserByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            $this->redirectWithError('/login', 'Nieprawidłowy email lub hasło');
            return;
        }

        if ($user->isBlocked()) {
            $this->redirectWithError('/login', 'Twoje konto zostało zablokowane. Skontaktuj się z administratorem.');
            return;
        }

        $this->userRepository->updateLastLogin($user->getId());

        AuthMiddleware::login([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'is_admin' => $user->isAdmin()
        ]);

        if ($user->isAdmin()) {
            $this->redirect('/admin');
        } else {
            $this->redirect('/dashboard');
        }
    }


    #[HttpMethod(['GET', 'POST'])]
    public function register(): void
    {
        if ($this->isGet()) {
            $error = $this->getFlash('error');
            $this->render('register', $error ? ['error' => $error] : []);
            return;
        }

        if (!$this->validateCsrf()) {
            $this->redirectWithError('/register', 'Nieprawidłowe żądanie. Odśwież stronę i spróbuj ponownie.');
            return;
        }

        $email = $this->sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $name = $this->sanitize($_POST['name'] ?? '');
        $surname = $this->sanitize($_POST['surname'] ?? '');

        if (empty($email) || empty($password) || empty($passwordConfirm) || empty($name) || empty($surname)) {
            $this->redirectWithError('/register', 'Wypełnij wszystkie pola');
            return;
        }

        if (!$this->isValidEmail($email)) {
            $this->redirectWithError('/register', 'Nieprawidłowy format email');
            return;
        }

        if (strlen($email) > 255 || strlen($name) > 100 || strlen($surname) > 100) {
            $this->redirectWithError('/register', 'Dane wejściowe są za długie');
            return;
        }

        if (!preg_match('/^[\p{L}\s\-]{2,}$/u', $name) || !preg_match('/^[\p{L}\s\-]{2,}$/u', $surname)) {
            $this->redirectWithError('/register', 'Imię i nazwisko mogą zawierać tylko litery (min. 2 znaki)');
            return;
        }

        if (strlen($password) < 8) {
            $this->redirectWithError('/register', 'Hasło musi mieć minimum 8 znaków');
            return;
        }

        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $this->redirectWithError('/register', 'Hasło musi zawierać wielką literę, małą literę i cyfrę');
            return;
        }

        if ($password !== $passwordConfirm) {
            $this->redirectWithError('/register', 'Hasła nie są identyczne');
            return;
        }

        if ($this->userRepository->emailExists($email)) {
            $this->redirectWithError('/register', 'Jeśli email istnieje, wysłano link aktywacyjny na podany email.');
            return;
        }

        try {
            $result = $this->userRepository->createUser($email, $password, $name, $surname);
            
            if ($result) {
                $this->redirect('/login?registered=1');
            } else {
                $this->redirectWithError('/register', 'Błąd podczas rejestracji. Spróbuj ponownie.');
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->redirectWithError('/register', 'Wystąpił błąd. Spróbuj ponownie później.');
        }
    }

    #[HttpMethod(['GET', 'POST'])]
    public function logout(): void
    {
        AuthMiddleware::logout();
        $this->redirect('/login');
    }
}