<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class User
{
    public function __construct(private PDO $pdo)
    {
    }

    public function addUser(string $nom, string $email, string $motDePasse): bool
    {
        if (empty(trim($nom)) || empty(trim($email)) || strlen($motDePasse) < 8) {
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO user (nom, email, mot_de_passe) 
            VALUES (:nom, :email, :mot_de_passe)
        ");
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mot_de_passe', $motDePasse);

        return $stmt->execute();
    }

    public function getUserByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function changeName(int $id, string $newName): bool
    {
        if (empty(trim($newName))) {
            return false;
        }
        $stmt = $this->pdo->prepare("UPDATE user SET nom = :nom WHERE id = :id");
        $stmt->bindParam(':nom', $newName);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function changePassword(int $id, string $newPasswordHash): bool
    {
        $stmt = $this->pdo->prepare("UPDATE user SET mot_de_passe = :mot_de_passe WHERE id = :id");
        $stmt->bindParam(':mot_de_passe', $newPasswordHash);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function deleteUser(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM user WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
