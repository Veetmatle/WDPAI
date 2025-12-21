<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

/**
 * Security Controller
 * Handles authentication: login, register, logout
 */
class SecurityController extends AppController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = UserRepository::getInstance();
    }

    /**
     * Handle login
     */
    public function login(): void
    {
        if ($this->isGet()) {
            $this->render('login');
            return;
        }

        // Validate CSRF
        if (!$this->validateCsrf()) {
            $this->render('login', ['error' => 'Nieprawidłowe żądanie. Odśwież stronę i spróbuj ponownie.']);
            return;
        }

        $email = $this->sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($email) || empty($password)) {
            $this->render('login', ['error' => 'Wypełnij wszystkie pola']);
            return;
        }

        // Validate email format
        if (!$this->isValidEmail($email)) {
            $this->render('login', ['error' => 'Nieprawidłowy format email']);
            return;
        }

        // Limit input length
        if (strlen($email) > 255 || strlen($password) > 255) {
            $this->render('login', ['error' => 'Nieprawidłowe dane wejściowe']);
            return;
        }

        // Get user from database
        $user = $this->userRepository->getUserByEmail($email);

        // Use consistent error message to prevent email enumeration
        if (!$user || !$user->verifyPassword($password)) {
            $this->render('login', ['error' => 'Nieprawidłowy email lub hasło']);
            return;
        }

        // Login successful - set session
        AuthMiddleware::login([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'surname' => $user->getSurname()
        ]);

        $this->redirect('/dashboard');
    }

    /**
     * Handle registration
     */
    public function register(): void
    {
        if ($this->isGet()) {
            $this->render('register');
            return;
        }

        // Validate CSRF
        if (!$this->validateCsrf()) {
            $this->render('register', ['error' => 'Nieprawidłowe żądanie. Odśwież stronę i spróbuj ponownie.']);
            return;
        }

        $email = $this->sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $name = $this->sanitize($_POST['name'] ?? '');
        $surname = $this->sanitize($_POST['surname'] ?? '');

        // Validate required fields
        if (empty($email) || empty($password) || empty($passwordConfirm) || empty($name) || empty($surname)) {
            $this->render('register', ['error' => 'Wypełnij wszystkie pola']);
            return;
        }

        // Validate email format
        if (!$this->isValidEmail($email)) {
            $this->render('register', ['error' => 'Nieprawidłowy format email']);
            return;
        }

        // Validate input lengths
        if (strlen($email) > 255 || strlen($name) > 100 || strlen($surname) > 100) {
            $this->render('register', ['error' => 'Dane wejściowe są za długie']);
            return;
        }

        // Validate name and surname (only letters, Polish characters, spaces, hyphens)
        if (!preg_match('/^[\p{L}\s\-]{2,}$/u', $name) || !preg_match('/^[\p{L}\s\-]{2,}$/u', $surname)) {
            $this->render('register', ['error' => 'Imię i nazwisko mogą zawierać tylko litery (min. 2 znaki)']);
            return;
        }

        // Validate password
        if (strlen($password) < 8) {
            $this->render('register', ['error' => 'Hasło musi mieć minimum 8 znaków']);
            return;
        }

        // Check password complexity
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $this->render('register', ['error' => 'Hasło musi zawierać wielką literę, małą literę i cyfrę']);
            return;
        }

        // Validate password confirmation
        if ($password !== $passwordConfirm) {
            $this->render('register', ['error' => 'Hasła nie są identyczne']);
            return;
        }

        // Check if email already exists
        if ($this->userRepository->emailExists($email)) {
            $this->render('register', ['error' => 'Ten adres email jest już zarejestrowany']);
            return;
        }

        // Create user
        try {
            $result = $this->userRepository->createUser($email, $password, $name, $surname);
            
            if ($result) {
                $this->redirect('/login?registered=1');
            } else {
                $this->render('register', ['error' => 'Błąd podczas rejestracji. Spróbuj ponownie.']);
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $this->render('register', ['error' => 'Wystąpił błąd. Spróbuj ponownie później.']);
        }
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        AuthMiddleware::logout();
        $this->redirect('/login');
    }
}