<?php
/**
 * GŁÓWNY PUNKT APLIKACJI (Front Controller)
 * * obsługuje każde żądanie wchodzące do systemu:
 * 1. Konfiguracja: Zabezpiecza ciasteczka sesji i ukrywa błędy przed użytkownikiem (bezpieczeństwo).
 * 2. Inicjalizacja: Startuje sesję, aby pamiętać zalogowanego użytkownika.
 * 3. Jeśli adres jest pusty, przekierowuje na Dashboard (zalogowany) lub Login (gość).
 * 4. Uruchomienie: Przekazuje konkretną ścieżkę do Routera, który ładuje odpowiednią podstronę.
 */

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);

session_start();

// Errory do logów
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once 'Routing.php';

$path = trim($_SERVER['REQUEST_URI'], '/');
$path = parse_url($path, PHP_URL_PATH);

// Redirekt
if (empty($path) || $path === 'index.php') {
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
    }
    exit();
}

// Controllery odpowiednio rutowane
Routing::run($path);