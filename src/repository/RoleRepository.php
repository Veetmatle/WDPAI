<?php

require_once 'Repository.php';

class RoleRepository extends Repository
{
    private static ?RoleRepository $instance = null;

    public const ROLE_USER = 1;
    public const ROLE_PREMIUM = 2;
    public const ROLE_BLOCKED = 3;
    public const ROLE_ADMIN = 4;

    public static function getInstance(): RoleRepository
    {
        if (self::$instance === null) {
            self::$instance = new RoleRepository();
        }
        return self::$instance;
    }

    public function getAllRoles(): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, name, display_name, description 
            FROM roles 
            ORDER BY id
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoleById(int $id): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, name, display_name, description 
            FROM roles 
            WHERE id = :id
        ');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getRoleByName(string $name): ?array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, name, display_name, description 
            FROM roles 
            WHERE name = :name
        ');
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getPermissionsForRole(int $roleId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT p.id, p.name, p.display_name, p.description
            FROM permissions p
            INNER JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id
        ');
        $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPermissionNamesForRole(int $roleId): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT p.name
            FROM permissions p
            INNER JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id
        ');
        $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function roleHasPermission(int $roleId, string $permissionName): bool
    {
        $stmt = $this->database->connect()->prepare('
            SELECT COUNT(*) 
            FROM role_permissions rp
            INNER JOIN permissions p ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id AND p.name = :permission_name
        ');
        $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
        $stmt->bindParam(':permission_name', $permissionName, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function getAllPermissions(): array
    {
        $stmt = $this->database->connect()->prepare('
            SELECT id, name, display_name, description 
            FROM permissions 
            ORDER BY id
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isBlocked(int $roleId): bool
    {
        return $roleId === self::ROLE_BLOCKED;
    }

    public function isAdmin(int $roleId): bool
    {
        return $roleId === self::ROLE_ADMIN;
    }

    public function isPremium(int $roleId): bool
    {
        return $roleId === self::ROLE_PREMIUM;
    }
}
