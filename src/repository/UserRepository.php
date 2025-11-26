<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/User.php';

class UserRepository extends Repository
{
    private static ?UserRepository $instance = null;

    // Singleton pattern
    public static function getInstance(): UserRepository
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getUsers(): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM users 
        ');

        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }

    public function getUser(string $email): ?User
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM users WHERE email = :email
        ');
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user == false) {
            return null;
        }

        return new User(
            $user['email'],
            $user['password'],
            $user['name'],
            $user['surname']
        );
    }

    public function addUser(string $email, string $password, string $name, string $surname): bool
    {
        $conn = $this->database->connect();
        
        // Hashowanie hasła
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare('
            INSERT INTO users (email, password, name, surname) 
            VALUES (:email, :password, :name, :surname)
        ');
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
        
        return $stmt->execute();
    }
}