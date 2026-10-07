<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class Commande
{
    private const STATUTS_VALIDES = ['en_attente', 'payee', 'expediee', 'livree', 'annulee'];

    public function __construct(private PDO $pdo)
    {
    }

    public function getCommandes(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM commandes ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCommandeById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM commandes WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $commande = $stmt->fetch(PDO::FETCH_ASSOC);
        return $commande ?: null;
    }

    public function getCommandesByUserId(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM commandes WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCommande(int $userId, ?int $adresseId = null, float $total = 0.0, string $statut = 'en_attente'): bool
    {
        if (!in_array($statut, self::STATUTS_VALIDES, true)) {
            return false;
        }

        if ($total < 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("INSERT INTO commandes (user_id, adresse_id, statut, total) VALUES (:user_id, :adresse_id, :statut, :total)");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':adresse_id', $adresseId, $adresseId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':total', $total);
        return $stmt->execute();
    }

    public function updateStatut(int $id, string $statut): bool
    {
        if (!in_array($statut, self::STATUTS_VALIDES, true)) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE commandes SET statut = :statut WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':statut', $statut);
        return $stmt->execute();
    }

    public function updateTotal(int $id, float $total): bool
    {
        if ($total < 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE commandes SET total = :total WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':total', $total);
        return $stmt->execute();
    }

    public function deleteCommande(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM commandes WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
