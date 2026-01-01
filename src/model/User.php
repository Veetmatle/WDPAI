<?php

class User 
{
    private int $id;
    private string $email;
    private string $passwordHash;
    private ?string $name;
    private ?string $surname;
    private int $roleId;
    private ?string $roleName;
    private ?string $lastLogin;
    private ?string $createdAt;

    public const ROLE_USER = 1;
    public const ROLE_PREMIUM = 2;
    public const ROLE_BLOCKED = 3;
    public const ROLE_ADMIN = 4;

    public function __construct(
        int $id,
        string $email,
        string $passwordHash,
        ?string $name = null,
        ?string $surname = null,
        int $roleId = self::ROLE_USER,
        ?string $roleName = null,
        ?string $lastLogin = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->name = $name;
        $this->surname = $surname;
        $this->roleId = $roleId;
        $this->roleName = $roleName;
        $this->lastLogin = $lastLogin;
        $this->createdAt = $createdAt;
    }

    public function getId(): int 
    {
        return $this->id;
    }

    public function getEmail(): string 
    {
        return $this->email;
    }

    public function getName(): ?string 
    {
        return $this->name;
    }

    public function getSurname(): ?string 
    {
        return $this->surname;
    }

    public function getFullName(): string 
    {
        return trim(($this->name ?? '') . ' ' . ($this->surname ?? ''));
    }

    public function getRoleId(): int 
    {
        return $this->roleId;
    }

    public function getRoleName(): ?string 
    {
        return $this->roleName;
    }

    public function isAdmin(): bool 
    {
        return $this->roleId === self::ROLE_ADMIN;
    }

    public function isBlocked(): bool 
    {
        return $this->roleId === self::ROLE_BLOCKED;
    }

    public function isPremium(): bool 
    {
        return $this->roleId === self::ROLE_PREMIUM;
    }

    public function isUser(): bool 
    {
        return $this->roleId === self::ROLE_USER;
    }

    public function getLastLogin(): ?string 
    {
        return $this->lastLogin;
    }

    public function getCreatedAt(): ?string 
    {
        return $this->createdAt;
    }

    public function verifyPassword(string $password): bool 
    {
        return password_verify($password, $this->passwordHash);
    }

    public function toArray(): array 
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'surname' => $this->surname,
            'role_id' => $this->roleId,
            'role_name' => $this->roleName,
            'is_admin' => $this->isAdmin(),
            'is_blocked' => $this->isBlocked(),
            'is_premium' => $this->isPremium(),
            'last_login' => $this->lastLogin,
            'created_at' => $this->createdAt
        ];
    }
}