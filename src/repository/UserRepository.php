<?php

require_once 'Repository.php';

// TODO: do poprawienia logowanie przez getUser - zeby zwracalo usera lub null (w security controller)
// TODO: dorobić hashowanie
// TODO: Dorobić tabelę na karty i tam je wrzucać
// TODO: Dorobić tabelę na logi
// TODO: UserRepository powinno być singletonem
class UserRepository extends Repository
{
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
            SELECT * FROM users where email = :email
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
}