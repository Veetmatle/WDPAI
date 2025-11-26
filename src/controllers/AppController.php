<?php

class AppController 
{
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function render(string $view, array $data = [])
    {
        extract($data);
        
        // Zmiana na ścieżkę absolutną
        $templatePath = __DIR__ . '/../../public/views/' . $view . '.html';

        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            die("View not found: " . $templatePath); 
        }
    }

    protected function redirect(string $path): void
    {
        header("Location: $path");
        exit();
    }
}