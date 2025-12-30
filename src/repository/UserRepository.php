<?php

require_once 'Repository.php';
require_once __DIR__ . '/../model/User.php';

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

    public function getUserByEmail(string $email): ?User
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, email, password_hash, name, surname, is_admin, is_blocked, last_login, created_at 
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
            (bool) $row['is_admin'],
            (bool) $row['is_blocked'],
            $row['last_login'],
            $row['created_at']
        );
    }

    public function getUserById(int $id): ?User
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, email, password_hash, name, surname, is_admin, is_blocked, last_login, created_at 
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
            (bool) $row['is_admin'],
            (bool) $row['is_blocked'],
            $row['last_login'],
            $row['created_at']
        );
    }

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

    public function emailExists(string $email): bool
    {
        $stmt = $this->database->connect()->prepare('
            SELECT COUNT(*) FROM users WHERE email = :email
        ');
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }

    public function updateLastLogin(int $id): bool
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE users 
            SET last_login = CURRENT_TIMESTAMP 
            WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAllUsers(): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, email, name, surname, is_admin, is_blocked, last_login, created_at 
            FROM users 
            ORDER BY created_at DESC
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setUserBlocked(int $id, bool $blocked): bool
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE users 
            SET is_blocked = :blocked 
            WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':blocked', $blocked, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function deleteUser(int $id): bool
    {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM users WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function createUserWithAdmin(string $email, string $password, string $name, string $surname, bool $isAdmin = false): bool
    {
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        
        $stmt = $this->database->connect()->prepare('
            INSERT INTO users (email, password_hash, name, surname, is_admin) 
            VALUES (:email, :password_hash, :name, :surname, :is_admin)
        ');
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password_hash', $passwordHash, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
        $stmt->bindParam(':is_admin', $isAdmin, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }

    public function setUserAdmin(int $id, bool $isAdmin): bool
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE users 
            SET is_admin = :is_admin 
            WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':is_admin', $isAdmin, PDO::PARAM_BOOL);
        return $stmt->execute();
    }
}