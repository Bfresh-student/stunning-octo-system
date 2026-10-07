<?php

declare(strict_types=1);

use App\Utils\Csrf;

$produits = $produits ?? [];
$baseUrl = $baseUrl ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue des Produits</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 30px auto; padding: 20px; background-color: #f9f9f9; color: #333; }
        .nav-bar { display: flex; justify-content: space-between; align-items: center; background: white; padding: 12px 20px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .nav-links a { margin-right: 18px; color: #007bff; text-decoration: none; font-weight: bold; }
        .nav-links a:hover { text-decoration: underline; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 8px 14px; border-radius: 4px; text-decoration: none; font-size: 14px; font-weight: bold; cursor: pointer; border: none; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-edit { background: #ffc107; color: #212529; }
        .btn-edit:hover { background: #e0a800; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-delete:hover { background: #c82333; }
        .btn-cart { background: #007bff; color: white; }
        .btn-cart:hover { background: #0069d9; }
        .btn-disabled { background: #6c757d; color: white; cursor: not-allowed; opacity: 0.65; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; vertical-align: middle; }
        th { background: #f1f3f5; color: #495057; font-weight: bold; text-transform: uppercase; font-size: 13px; }
        tr:hover { background-color: #f8f9fa; }
        .img-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-in-stock { background: #e8f5e9; color: #2e7d32; }
        .badge-out-of-stock { background: #ffebee; color: #c62828; }
        .actions-cell { white-space: nowrap; }
        .empty-state { text-align: center; padding: 40px; background: white; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="nav-bar">
        <div class="nav-links">
            <a href="<?= htmlspecialchars((string) $baseUrl) ?>/produits">Boutique</a>
            <a href="<?= htmlspecialchars((string) $baseUrl) ?>/panier">Mon Panier</a>
            <a href="<?= htmlspecialchars((string) $baseUrl) ?>/commandes">Mes Commandes</a>
            <a href="<?= htmlspecialchars((string) $baseUrl) ?>/adresses">Mes Adresses</a>
        </div>
        <div>
            <a href="<?= htmlspecialchars((string) $baseUrl) ?>/profile" class="btn btn-edit">Mon Compte</a>
        </div>
    </div>

    <div class="header">
        <div>
            <h2>Catalogue des Produits</h2>
            <p style="margin: 0; color: #6c757d;">Total : <?= count($produits) ?> produit(s) disponible(s)</p>
        </div>
        <a href="<?= htmlspecialchars((string) $baseUrl) ?>/produits/ajouter" class="btn btn-success">+ Ajouter un nouveau produit</a>
    </div>

    <?php if (empty($produits)) : ?>
        <div class="empty-state">
            <p style="font-size: 18px; color: #6c757d;">Aucun produit n'est disponible pour le moment.</p>
            <a href="<?= htmlspecialchars((string) $baseUrl) ?>/produits/ajouter" class="btn btn-success">Créer le premier produit</a>
        </div>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produits as $p) : ?>
                    <?php
                        $stock = (int) ($p['stock'] ?? 0);
                        $imgSrc = (string) ($p['image_url'] ?? '');
                        $fullImgUrl = '';
                        if (!empty($imgSrc)) {
                            $fullImgUrl = str_starts_with($imgSrc, 'http') ? $imgSrc : $baseUrl . $imgSrc;
                        }
                    ?>
                    <tr>
                        <td>
                            <?php if (!empty($fullImgUrl)) : ?>
                                <img src="<?= htmlspecialchars((string) $fullImgUrl) ?>" alt="<?= htmlspecialchars((string) ($p['nom'] ?? 'Produit')) ?>" class="img-thumb">
                            <?php else : ?>
                                <span style="color: #999; font-size: 12px;">Pas d'image</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars((string) ($p['nom'] ?? '')) ?></strong>
                        </td>
                        <td style="max-width: 250px; color: #555;">
                            <?= htmlspecialchars((string) ($p['description'] ?? '')) ?>
                        </td>
                        <td style="font-size: 16px; font-weight: bold; color: #007bff;">
                            <?= number_format((float) ($p['prix'] ?? 0), 2) ?> $
                        </td>
                        <td>
                            <?php if ($stock > 0) : ?>
                                <span class="badge badge-in-stock"><?= $stock ?> en stock</span>
                            <?php else : ?>
                                <span class="badge badge-out-of-stock">Rupture</span>
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell" style="text-align: right;">
                            <!-- Ajouter au panier -->
                            <?php if ($stock > 0) : ?>
                                <form method="post" action="<?= htmlspecialchars((string) $baseUrl) ?>/panier/ajouter" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) Csrf::generateToken()) ?>">
                                    <input type="hidden" name="produit_id" value="<?= (int) ($p['id'] ?? 0) ?>">
                                    <input type="hidden" name="quantite" value="1">
                                    <button type="submit" class="btn btn-cart" title="Ajouter 1 au panier">Ajouter</button>
                                </form>
                            <?php else : ?>
                                <button type="button" class="btn btn-disabled" disabled>Épuisé</button>
                            <?php endif; ?>

                            <!-- Modifier -->
                            <a href="<?= htmlspecialchars((string) $baseUrl) ?>/produits/modifier/<?= (int) ($p['id'] ?? 0) ?>" class="btn btn-edit">Modifier</a>

                            <!-- Supprimer -->
                            <form method="post" action="<?= htmlspecialchars((string) $baseUrl) ?>/produits/supprimer/<?= (int) ($p['id'] ?? 0) ?>" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) Csrf::generateToken()) ?>">
                                <button type="submit" class="btn btn-delete">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
