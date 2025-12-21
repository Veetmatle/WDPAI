<?php

/**
 * Authentication Middleware
 * Handles user authentication checks and session management
 */
class AuthMiddleware
{
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get current user ID
     */
    public static function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user data
     */
    public static function getUser(): ?array
    {
        if (!self::isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'] ?? '',
            'name' => $_SESSION['user_name'] ?? '',
            'surname' => $_SESSION['user_surname'] ?? ''
        ];
    }

    /**
     * Set user session after successful login
     */
    public static function login(array $userData): void
    {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_name'] = $userData['name'];
        $_SESSION['user_surname'] = $userData['surname'];
        $_SESSION['login_time'] = time();
    }

    /**
     * Clear user session on logout
     */
    public static function logout(): void
    {
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }
        
        session_destroy();
    }

    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}