<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/User.php';

/**
 * User Repository
 * Handles all database operations for users
 */
class UserRepository extends Repository
{
    private static ?UserRepository $instance = null;

    public static function getInstance(): UserRepository
    {
        if (self::$instance === null) {
            self::$instance = new UserRepository();
        }
        return self::$instance;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail(string $email): ?User
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, email, password_hash, name, surname, created_at 
            FROM users 
            WHERE email = :email
        ');
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new User(
            $row['id'],
            $row['email'],
            $row['password_hash'],
            $row['name'],
            $row['surname'],
            $row['created_at']
        );
    }

    /**
     * Get user by ID
     */
    public function getUserById(int $id): ?User
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, email, password_hash, name, surname, created_at 
            FROM users 
            WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new User(
            $row['id'],
            $row['email'],
            $row['password_hash'],
            $row['name'],
            $row['surname'],
            $row['created_at']
        );
    }

    /**
     * Create new user
     */
    public function createUser(string $email, string $password, string $name, string $surname): bool
    {
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        
        $stmt = $this->database->connect()->prepare('
            INSERT INTO users (email, password_hash, name, surname) 
            VALUES (:email, :password_hash, :name, :surname)
        ');
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password_hash', $passwordHash, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Update user profile
     */
    public function updateUser(int $id, string $name, string $surname): bool
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE users 
            SET name = :name, surname = :surname 
            WHERE id = :id
        ');
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Update user password
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
        
        $stmt = $this->database->connect()->prepare('
            UPDATE users 
            SET password_hash = :password_hash 
            WHERE id = :id
        ');
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':password_hash', $passwordHash, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->database->connect()->prepare('
            SELECT COUNT(*) FROM users WHERE email = :email
        ');
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
}