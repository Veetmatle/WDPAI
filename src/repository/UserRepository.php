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
            SELECT u.id, u.email, u.password_hash, u.name, u.surname, u.role_id, r.name as role_name, u.last_login, u.created_at 
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.email = :email
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
            (int) $row['role_id'],
            $row['role_name'],
            $row['last_login'],
            $row['created_at']
        );
    }

    public function getUserById(int $id): ?User
    {
        $stmt = $this->database->connect()->prepare('
            SELECT u.id, u.email, u.password_hash, u.name, u.surname, u.role_id, r.name as role_name, u.last_login, u.created_at 
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id
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
            (int) $row['role_id'],
            $row['role_name'],
            $row['last_login'],
            $row['created_at']
        );
    }

    public function createUser(string $email, string $password, string $name, string $surname, int $roleId = User::ROLE_USER): bool
    {
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        
        $stmt = $this->database->connect()->prepare('
            INSERT INTO users (email, password_hash, name, surname, role_id) 
            VALUES (:email, :password_hash, :name, :surname, :role_id)
        ');
        
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password_hash', $passwordHash, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
        $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
        
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
            SELECT u.id, u.email, u.name, u.surname, u.role_id, r.name as role_name, r.display_name as role_display_name, u.last_login, u.created_at 
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            ORDER BY u.created_at DESC
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setUserRole(int $id, int $roleId): bool
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE users 
            SET role_id = :role_id 
            WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function setUserBlocked(int $id, bool $blocked): bool
    {
        $roleId = $blocked ? User::ROLE_BLOCKED : User::ROLE_USER;
        return $this->setUserRole($id, $roleId);
    }

    public function deleteUser(int $id): bool
    {
        $stmt = $this->database->connect()->prepare('
            DELETE FROM users WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function createUserWithRole(string $email, string $password, string $name, string $surname, int $roleId = User::ROLE_USER): bool
    {
        return $this->createUser($email, $password, $name, $surname, $roleId);
    }

    public function setUserAdmin(int $id, bool $isAdmin): bool
    {
        $roleId = $isAdmin ? User::ROLE_ADMIN : User::ROLE_USER;
        return $this->setUserRole($id, $roleId);
    }

    public function getUserPermissions(int $userId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT p.name
            FROM permissions p
            INNER JOIN role_permissions rp ON p.id = rp.permission_id
            INNER JOIN users u ON u.role_id = rp.role_id
            WHERE u.id = :user_id
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function userHasPermission(int $userId, string $permissionName): bool
    {
        $stmt = $this->database->connect()->prepare('
            SELECT COUNT(*) 
            FROM permissions p
            INNER JOIN role_permissions rp ON p.id = rp.permission_id
            INNER JOIN users u ON u.role_id = rp.role_id
            WHERE u.id = :user_id AND p.name = :permission_name
        ');
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':permission_name', $permissionName, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}