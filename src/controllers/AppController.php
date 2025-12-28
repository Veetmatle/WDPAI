<?php

require_once __DIR__ . '/../middleware/AuthMiddleware.php';

/**
 * Base Application Controller
 * Provides common functionality for all controllers
 */
abstract class AppController 
{
    public function __construct()
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if request method is POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request method is GET
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Require user to be logged in
     */
    protected function requireLogin(): void
    {
        if (!AuthMiddleware::isAuthenticated()) {
            header('Location: /login');
            exit();
        }
    }

    /**
     * Render a view template
     */
    protected function render(string $view, array $data = []): void
    {
        // Add CSRF token to all views
        $data['csrf_token'] = AuthMiddleware::generateCsrfToken();
        
        // Add user data if authenticated
        if (AuthMiddleware::isAuthenticated()) {
            $data['user'] = AuthMiddleware::getUser();
        }
        
        // Extract data to variables
        extract($data);
        
        $templatePath = __DIR__ . '/../../public/views/' . $view . '.php';

        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            http_response_code(500);
            echo "View not found: " . htmlspecialchars($view);
        }
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $path): void
    {
        header("Location: $path");
        exit();
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Get current user ID
     */
    protected function getUserId(): ?int
    {
        return AuthMiddleware::getUserId();
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrf(?string $token = null): bool
    {
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }
        return AuthMiddleware::validateCsrfToken($token);
    }

    /**
     * Sanitize string input
     */
    protected function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email format
     */
    protected function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Get POST data
     */
    protected function getPostData(): array
    {
        return $_POST;
    }

    /**
     * Get GET data
     */
    protected function getQueryParams(): array
    {
        return $_GET;
    }

    /**
     * Get JSON body data
     */
    protected function getJsonBody(): array
    {
        $content = file_get_contents('php://input');
        return json_decode($content, true) ?? [];
    }

    /**
     * Set a flash message (displayed once, then removed)
     */
    protected function setFlash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    /**
     * Get and remove a flash message
     */
    protected function getFlash(string $key): ?string
    {
        $message = $_SESSION['flash'][$key] ?? null;
        if ($message !== null) {
            unset($_SESSION['flash'][$key]);
        }
        return $message;
    }

    /**
     * Redirect with an error flash message
     */
    protected function redirectWithError(string $path, string $error): void
    {
        $this->setFlash('error', $error);
        $this->redirect($path);
    }
}