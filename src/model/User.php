<?php

/**
 * User Model
 * Represents a user entity
 */
class User 
{
    private int $id;
    private string $email;
    private string $passwordHash;
    private ?string $name;
    private ?string $surname;
    private ?string $createdAt;

    public function __construct(
        int $id,
        string $email,
        string $passwordHash,
        ?string $name = null,
        ?string $surname = null,
        ?string $createdAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->name = $name;
        $this->surname = $surname;
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
            'created_at' => $this->createdAt
        ];
    }
}