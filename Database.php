<?php

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    
    private string $username;
    private string $password;
    private string $host;
    private string $database;

    private function __construct()
    {
        $this->loadEnv();
        
        $this->username = $_ENV['DB_USERNAME'] ?? '';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->database = $_ENV['DB_DATABASE'] ?? '';
    }

    // Zabronienie klonowania (część wzorca Singleton)
    private function __clone() {}

    // Zabronienie deserializacji (część wzorca Singleton)
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadEnv(): void
    {
        $envFile = __DIR__ . '/.env';
        
        if (!file_exists($envFile)) {
            $this->handleError("File .env not found");
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Pomiń komentarze
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parsuj linię KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Usuń cudzysłowy jeśli istnieją
                $value = trim($value, '"\'');
                
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    public function connect(): PDO
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        try {
            $this->connection = new PDO(
                "pgsql:host=$this->host;port=5432;dbname=$this->database",
                $this->username,
                $this->password,
                ["sslmode" => "prefer"]
            );

            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->connection;
        }
        catch(PDOException $e) {
            $this->handleError("Database connection failed: " . $e->getMessage());
        }
    }

    public function disconnect(): void
    {
        $this->connection = null;
    }

    private function handleError(string $message): void
    {
        error_log($message);
        
        http_response_code(500);
        
        // Sprawdź czy istnieje dedykowany plik z błędem
        $errorPage = __DIR__ . '/views/error500.php';
        
        if (file_exists($errorPage)) {
            require_once $errorPage;
        } else {
            // Fallback jeśli nie ma dedykowanego pliku
            echo '<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Internal Server Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .error-container {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #d32f2f;
            font-size: 72px;
            margin: 0;
        }
        h2 {
            color: #333;
            margin: 10px 0;
        }
        p {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>500</h1>
        <h2>Internal Server Error</h2>
        <p>Wystąpił problem z serwerem. Spróbuj ponownie później.</p>
    </div>
</body>
</html>';
        }
        exit;
    }
}