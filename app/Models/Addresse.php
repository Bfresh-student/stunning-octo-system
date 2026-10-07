<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Addresse
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getAdresses(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM adresses");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAdresseById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM adresses WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $adresse = $stmt->fetch(PDO::FETCH_ASSOC);
        return $adresse ?: null;
    }

    public function getAdressesByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM adresses WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addAdresse(int $userId, string $rue, string $ville, ?string $codePostal = null, string $pays = 'Haïti', ?string $telephone = null): bool
    {
        if (empty(trim($rue)) || empty(trim($ville))) {
            return false;
        }

        $stmt = $this->pdo->prepare("INSERT INTO adresses (user_id, rue, ville, code_postal, pays, telephone) VALUES (:user_id, :rue, :ville, :code_postal, :pays, :telephone)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':rue', $rue);
        $stmt->bindParam(':ville', $ville);
        $stmt->bindParam(':code_postal', $codePostal);
        $stmt->bindParam(':pays', $pays);
        $stmt->bindParam(':telephone', $telephone);
        return $stmt->execute();
    }

    public function updateAdresse(int $id, string $rue, string $ville, ?string $codePostal = null, string $pays = 'Haïti', ?string $telephone = null): bool
    {
        if (empty(trim($rue)) || empty(trim($ville))) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE adresses SET rue = :rue, ville = :ville, code_postal = :code_postal, pays = :pays, telephone = :telephone WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':rue', $rue);
        $stmt->bindParam(':ville', $ville);
        $stmt->bindParam(':code_postal', $codePostal);
        $stmt->bindParam(':pays', $pays);
        $stmt->bindParam(':telephone', $telephone);
        return $stmt->execute();
    }

    public function deleteAdresse(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM adresses WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
