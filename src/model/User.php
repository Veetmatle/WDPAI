<?php

class User {
    private string $email;
    private string $password;
    private string $name;
    private string $surname;
    private ?string $phone;

    public function __construct(
        string $email,
        string $password,
        string $name,
        string $surname,
        ?string $phone = null
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->surname = $surname;
        $this->phone = $phone;
    }

    public function getEmail(): string 
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function getFullName(): string
    {
        return $this->name . ' ' . $this->surname;
    }
}