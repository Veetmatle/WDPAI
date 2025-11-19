<?php

require_once 'AppController.php';

class SecurityController extends AppController
{
    // ======= LOKALNA "BAZA" UŻYTKOWNIKÓW =======
    // (Zostawiamy ją jako 'static' na razie, choć docelowo przeniesiemy to do repozytorium)
    private static array $users = [
        [
            'email' => 'anna@example.com',
            'password' => '$2y$10$wz2g9JrHYcF8bLGBbDkEXuJQAnl4uO9RV6cWJKcf.6uAEkhFZpU0i', // test123
            'first_name' => 'Anna'
        ],
        [
            'email' => 'bartek@example.com',
            'password' => '$2y$10$fK9rLobZK2C6rJq6B/9I6u6Udaez9CaRu7eC/0zT3pGq5piVDsElW', // haslo456
            'first_name' => 'Bartek'
        ],
    ];


    public function login()
    {
        if (!$this->isPost()) {
            return $this->render('login');
        }

        $email = $_POST["email"] ?? '';
        $password = $_POST["password"] ?? '';

        if (empty($email) || empty($password)) {
            // Zmieniamy komunikat na tablicę, jak mieliśmy wcześniej
            return $this->render('login', ['messages' => 'Wypełnij wszystkie pola!']);
        }

        // TODO: tutaj to do ogarnięcia z bazą danych
        $userRepository = new UserRepository();
        $user = $userRepository->getUser();
        var_dump($users);

        $userRow = null;
        foreach (self::$users as $u) {
            if (strcasecmp($u['email'], $email) === 0) {
                $userRow = $u;
                break;
            }
        }

        if (!$userRow) {
            return $this->render('login', ['messages' => 'Użytkownik nie znaleziony']);
        }

        if (!password_verify($password, $userRow['password'])) {
            return $this->render('login', ['messages' => 'Błędne hasło']);
        }

        // Używamy naszej nowej metody z AppController
        return $this->redirect('/dashboard');
    }

    public function register()
    {
        if (!$this->isPost()) {
            return $this->render('register');
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordRepeat = $_POST['password_repeat'] ?? ''; // <-- Pobieramy powtórzone hasło
        $firstName = $_POST['firstname'] ?? '';

        if (empty($email) || empty($password) || empty($passwordRepeat) || empty($firstName)) {
            return $this->render('register', ['messages' => 'Wypełnij wszystkie pola!']);
        }

        // --- DODANA WALIDACJA ---
        if ($password !== $passwordRepeat) {
            return $this->render('register', ['messages' => 'Hasła nie są identyczne!']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             return $this->render('register', ['messages' => 'Niepoprawny format email!']);
        }
        // --- KONIEC WALIDACJI ---


        foreach (self::$users as $u) {
            if (strcasecmp($u['email'], $email) === 0) {
                return $this->render('register', ['messages' => 'Ten email jest już zajęty']);
            }
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Dodajemy nowego użytkownika do naszej "bazy"
        self::$users[] = [
            'email' => $email,
            'password' => $hashedPassword,
            'first_name' => $firstName
        ];

        // Przekierowujemy do logowania po pomyślnej rejestracji
        return $this->redirect('/login');
    }
}