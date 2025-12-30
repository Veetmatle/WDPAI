<?php

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

abstract class AppController 
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function requireLogin(): void
    {
        if (!AuthMiddleware::isAuthenticated()) {
            header('Location: /login');
            exit();
        }
    }

    protected function render(string $view, array $data = []): void
    {
        $data['csrf_token'] = AuthMiddleware::generateCsrfToken();
        
        if (AuthMiddleware::isAuthenticated()) {
            $data['user'] = AuthMiddleware::getUser();
        }
        
        extract($data);
        
        $templatePath = __DIR__ . '/../../public/views/' . $view . '.php';

        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            http_response_code(500);
            echo "View not found: " . htmlspecialchars($view);
        }
    }

    protected function redirect(string $path): void
    {
        header("Location: $path");
        exit();
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    protected function getUserId(): ?int
    {
        return AuthMiddleware::getUserId();
    }

    protected function validateCsrf(?string $token = null): bool
    {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }
        return AuthMiddleware::validateCsrfToken($token);
    }

    protected function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function getPostData(): array
    {
        return $_POST;
    }

    protected function getQueryParams(): array
    {
        return $_GET;
    }

    protected function getJsonBody(): array
    {
        $content = file_get_contents('php://input');
        return json_decode($content, true) ?? [];
    }

    protected function setFlash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    protected function getFlash(string $key): ?string
    {
        $message = $_SESSION['flash'][$key] ?? null;
        if ($message !== null) {
            unset($_SESSION['flash'][$key]);
        }
        return $message;
    }

    protected function redirectWithError(string $path, string $error): void
    {
        $this->setFlash('error', $error);
        $this->redirect($path);
    }
}