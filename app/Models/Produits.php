<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Produits
{
    public function __construct(private PDO $pdo)
    {
    }
    public function getProduits(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM produits");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getProduitById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM produits WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);
        return $produit ?: null;
    }
    public function addProduit(string $nom, string $description, float $prix, int $stock, string $image): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO produits (nom, description, prix, stock, image_url) VALUES (:nom, :description, :prix, :stock, :image)");
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':prix', $prix);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':image', $image);
        if (empty(trim($nom)) || empty($description) || $prix < 0 || $stock < 0 || empty($image)) {
            return false;
        } elseif ($prix < 0) {
            return false;
        } elseif ($stock < 0) {
            return false;
        }
        $stmt->execute();
        return true;
    }

    public function updateProduit(int $id, string $nom, string $description, float $prix, int $stock, string $image): bool
    {
        $stmt = $this->pdo->prepare("UPDATE produits SET nom = :nom, description = :description, prix = :prix, stock = :stock, image_url = :image WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':prix', $prix);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':image', $image);
        if (empty(trim($nom)) || empty($description) || $prix < 0 || $stock < 0 || empty($image)) {
            return false;
        } elseif ($prix < 0) {
            return false;
        } elseif ($stock < 0) {
            return false;
        } elseif (empty($image)) {
            return false;
        }
        return $stmt->execute();
    }

    public function deleteProduit(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM produits WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
