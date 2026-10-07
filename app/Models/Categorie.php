<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Categorie
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getCategories(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM categories");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategorieById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $categorie = $stmt->fetch(PDO::FETCH_ASSOC);
        return $categorie ?: null;
    }

    public function getCategorieByNom(string $nom): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE nom = :nom");
        $stmt->bindParam(':nom', $nom);
        $stmt->execute();
        $categorie = $stmt->fetch(PDO::FETCH_ASSOC);
        return $categorie ?: null;
    }

    public function addCategorie(string $nom, ?string $description = null): bool
    {
        if (empty(trim($nom))) {
            return false;
        }

        $stmt = $this->pdo->prepare("INSERT INTO categories (nom, description) VALUES (:nom, :description)");
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }

    public function updateCategorie(int $id, string $nom, ?string $description = null): bool
    {
        if (empty(trim($nom))) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE categories SET nom = :nom, description = :description WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        return $stmt->execute();
    }

    public function deleteCategorie(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
