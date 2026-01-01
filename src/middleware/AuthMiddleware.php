<?php

class AuthMiddleware
{
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public static function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUser(): ?array
    {
        if (!self::isAuthenticated()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'] ?? '',
            'name' => $_SESSION['user_name'] ?? '',
            'surname' => $_SESSION['user_surname'] ?? '',
            'role_id' => $_SESSION['role_id'] ?? 1,
            'role_name' => $_SESSION['role_name'] ?? 'user'
        ];
    }

    public static function login(array $userData): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['user_email'] = $userData['email'];
        $_SESSION['user_name'] = $userData['name'];
        $_SESSION['user_surname'] = $userData['surname'];
        $_SESSION['role_id'] = $userData['role_id'] ?? 1;
        $_SESSION['role_name'] = $userData['role_name'] ?? 'user';
        $_SESSION['permissions'] = $userData['permissions'] ?? [];
        $_SESSION['login_time'] = time();
    }

    public static function getRoleId(): int
    {
        return $_SESSION['role_id'] ?? 1;
    }

    public static function getRoleName(): string
    {
        return $_SESSION['role_name'] ?? 'user';
    }

    public static function isAdmin(): bool
    {
        return self::getRoleId() === 4;
    }

    public static function isPremium(): bool
    {
        return self::getRoleId() === 2;
    }

    public static function isBlocked(): bool
    {
        return self::getRoleId() === 3;
    }

    public static function isUser(): bool
    {
        return self::getRoleId() === 1;
    }

    public static function hasPermission(string $permissionName): bool
    {
        $permissions = $_SESSION['permissions'] ?? [];
        return in_array($permissionName, $permissions, true);
    }

    public static function getPermissions(): array
    {
        return $_SESSION['permissions'] ?? [];
    }

    public static function canEditReceipts(): bool
    {
        return self::hasPermission('edit_receipts');
    }

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

    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}