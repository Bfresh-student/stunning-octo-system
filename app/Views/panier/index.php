<?php

declare(strict_types=1);

use App\Utils\Csrf;

$articles = $articles ?? [];
$total = $total ?? 0.0;
$baseUrl = $baseUrl ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; }
        .total-box { margin-top: 20px; font-size: 1.2em; font-weight: bold; text-align: right; }
        .btn { display: inline-block; padding: 8px 14px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
        .btn-danger { background: #dc3545; }
        .actions { margin-top: 20px; display: flex; justify-content: space-between; }
        .nav-bar { margin-bottom: 20px; }
        .nav-bar a { margin-right: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="nav-bar">
        <a href="<?= htmlspecialchars($baseUrl . '/profile') ?>">Mon Profil</a>
        <a href="<?= htmlspecialchars($baseUrl . '/produits') ?>">Boutique</a>
        <a href="<?= htmlspecialchars($baseUrl . '/commandes') ?>">Mes Commandes</a>
    </div>

    <h2>Mon Panier</h2>

    <?php if (empty($articles)) : ?>
        <p>Votre panier est vide.</p>
        <p><a href="<?= htmlspecialchars($baseUrl . '/produits') ?>">Découvrir nos produits</a></p>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $art) : ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($art['produit_nom'] ?? 'Produit') ?></strong></td>
                        <td><?= number_format((float) ($art['produit_prix'] ?? 0), 2) ?> $</td>
                        <td>
                            <form method="post" action="<?= htmlspecialchars($baseUrl . '/panier/modifier') ?>" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generateToken()) ?>">
                                <input type="hidden" name="produit_id" value="<?= (int) $art['produit_id'] ?>">
                                <input type="number" name="quantite" value="<?= (int) $art['quantite'] ?>" min="1" style="width: 50px;">
                                <button type="submit">OK</button>
                            </form>
                        </td>
                        <td><?= number_format(((float) ($art['produit_prix'] ?? 0)) * ((int) $art['quantite']), 2) ?> $</td>
                        <td>
                            <form method="post" action="<?= htmlspecialchars($baseUrl . '/panier/supprimer') ?>" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generateToken()) ?>">
                                <input type="hidden" name="produit_id" value="<?= (int) $art['produit_id'] ?>">
                                <button type="submit" class="btn btn-danger" style="font-size: 12px; padding: 4px 8px;">Retirer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-box">
            Total : <?= number_format((float) $total, 2) ?> $
        </div>

        <div class="actions">
            <form method="post" action="<?= htmlspecialchars($baseUrl . '/panier/vider') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generateToken()) ?>">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Vider le panier ?');">Vider le panier</button>
            </form>

            <form method="post" action="<?= htmlspecialchars($baseUrl . '/commandes/creer') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generateToken()) ?>">
                <button type="submit" class="btn">Valider la commande</button>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>

