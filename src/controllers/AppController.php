<?php

class AppController 
{
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function render(string $view, array $data = [])
    {
        // Twoja logika renderowania widoku
        extract($data);
        include "public/views/$view.php";
    }

    protected function redirect(string $path): void
    {
        header("Location: $path");
        exit();
    }
}