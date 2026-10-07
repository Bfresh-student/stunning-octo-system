<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class LigneCommandes
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getLignesByCommandeId(int $commandeId): array
    {
        $stmt = $this->pdo->prepare("SELECT lc.*, p.nom AS produit_nom FROM lignes_commande lc LEFT JOIN produits p ON lc.produit_id = p.id WHERE lc.commande_id = :commande_id");
        $stmt->bindParam(':commande_id', $commandeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLigneById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM lignes_commande WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $ligne = $stmt->fetch(PDO::FETCH_ASSOC);
        return $ligne ?: null;
    }

    public function addLigneCommande(int $commandeId, int $produitId, int $quantite, float $prixUnitaire): bool
    {
        if ($quantite <= 0 || $prixUnitaire < 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("INSERT INTO lignes_commande (commande_id, produit_id, quantite, prix_unitaire) VALUES (:commande_id, :produit_id, :quantite, :prix_unitaire)");
        $stmt->bindParam(':commande_id', $commandeId, PDO::PARAM_INT);
        $stmt->bindParam(':produit_id', $produitId, PDO::PARAM_INT);
        $stmt->bindParam(':quantite', $quantite, PDO::PARAM_INT);
        $stmt->bindParam(':prix_unitaire', $prixUnitaire);
        return $stmt->execute();
    }

    public function updateQuantite(int $id, int $quantite): bool
    {
        if ($quantite <= 0) {
            return false;
        }

        $stmt = $this->pdo->prepare("UPDATE lignes_commande SET quantite = :quantite WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':quantite', $quantite, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteLigneCommande(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM lignes_commande WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function deleteLignesByCommandeId(int $commandeId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM lignes_commande WHERE commande_id = :commande_id");
        $stmt->bindParam(':commande_id', $commandeId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
