<?php

require_once 'AppController.php';
require_once __DIR__.'/../repository/UserRepository.php';

class SecurityController extends AppController
{
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = UserRepository::getInstance();
    }

    public function login()
    {
        if (!$this->isPost()) {
            return $this->render('login');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->render('login', ['messages' => ['Wypełnij wszystkie pola!']]);
        }

        // Sprawdzenie czy user istnieje w bazie
        $user = $this->userRepository->getUser($email);

        if (!$user) {
            return $this->render('login', ['messages' => ['Nieprawidłowy email lub hasło']]);
        }

        // Weryfikacja hasła
        if (!$user->verifyPassword($password)) {
            return $this->render('login', ['messages' => ['Nieprawidłowy email lub hasło']]);
        }

        // Logowanie udane - rozpocznij sesję
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_name'] = $user->getName();

        // Przekierowanie do dashboardu
        return $this->redirect('/dashboard');
    }

    public function register()
    {
        if (!$this->isPost()) {
            return $this->render('register');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordRepeat = $_POST['password_repeat'] ?? '';
        $firstName = $_POST['firstname'] ?? '';
        $lastName = $_POST['lastname'] ?? '';

        if (empty($email) || empty($password) || empty($passwordRepeat) || empty($firstName) || empty($lastName)) {
            return $this->render('register', ['messages' => ['Wypełnij wszystkie pola!']]);
        }

        // Walidacja hasła
        if ($password !== $passwordRepeat) {
            return $this->render('register', ['messages' => ['Hasła nie są identyczne!']]);
        }

        // Walidacja email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render('register', ['messages' => ['Niepoprawny format email!']]);
        }

        // Sprawdzenie czy email już istnieje
        $existingUser = $this->userRepository->getUser($email);
        if ($existingUser) {
            return $this->render('register', ['messages' => ['Ten email jest już zajęty']]);
        }

        // Dodanie użytkownika do bazy danych
        try {
            $this->userRepository->addUser($email, $password, $firstName, $lastName);
            return $this->redirect('/login');
        } catch (Exception $e) {
            return $this->render('register', ['messages' => ['Błąd podczas rejestracji. Spróbuj ponownie.']]);
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        
        return $this->redirect('/login');
    }
}