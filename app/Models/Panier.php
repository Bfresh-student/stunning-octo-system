<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Panier
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getPanierByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.user_id, p.produit_id, p.quantite, p.added_at, pr.nom AS produit_nom, pr.prix AS produit_prix
            FROM panier p
            INNER JOIN produits pr ON p.produit_id = pr.id
            WHERE p.user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getItem(int $userId, int $produitId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM panier WHERE user_id = :user_id AND produit_id = :produit_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':produit_id', $produitId, PDO::PARAM_INT);
        $stmt->execute();
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        return $item ?: null;
    }

    public function ajouterProduit(int $userId, int $produitId, int $quantite = 1): bool
    {
        if ($quantite <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO panier (user_id, produit_id, quantite)
            VALUES (:user_id, :produit_id, :quantite)
            ON DUPLICATE KEY UPDATE quantite = quantite + :quantite_update
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':produit_id', $produitId, PDO::PARAM_INT);
        $stmt->bindParam(':quantite', $quantite, PDO::PARAM_INT);
        $stmt->bindParam(':quantite_update', $quantite, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function updateQuantite(int $userId, int $produitId, int $quantite): bool
    {
        if ($quantite <= 0) {
            return $this->supprimerProduit($userId, $produitId);
        }

        $stmt = $this->pdo->prepare("UPDATE panier SET quantite = :quantite WHERE user_id = :user_id AND produit_id = :produit_id");
        $stmt->bindParam(':quantite', $quantite, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':produit_id', $produitId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function supprimerProduit(int $userId, int $produitId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM panier WHERE user_id = :user_id AND produit_id = :produit_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':produit_id', $produitId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function viderPanier(int $userId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM panier WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getTotal(int $userId): float
    {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(p.quantite * pr.prix), 0) AS total
            FROM panier p
            INNER JOIN produits pr ON p.produit_id = pr.id
            WHERE p.user_id = :user_id
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float) ($result['total'] ?? 0);
    }
}
