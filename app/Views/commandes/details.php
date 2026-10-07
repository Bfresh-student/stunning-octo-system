<?php

declare(strict_types=1);

$commande = $commande ?? [];
$lignes = $lignes ?? [];
$baseUrl = $baseUrl ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Commande #<?= (int) ($commande['id'] ?? 0) ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; }
        .card { border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; }
        .nav-bar { margin-bottom: 20px; }
        .nav-bar a { margin-right: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="nav-bar">
        <a href="<?= htmlspecialchars($baseUrl . '/commandes') ?>">← Retour aux commandes</a>
        <a href="<?= htmlspecialchars($baseUrl . '/produits') ?>">Boutique</a>
    </div>

    <div class="card">
        <h2>Commande #<?= (int) ($commande['id'] ?? 0) ?></h2>
        <p><strong>Date :</strong> <?= htmlspecialchars($commande['created_at'] ?? '') ?></p>
        <p><strong>Statut :</strong> <?= htmlspecialchars($commande['statut'] ?? '') ?></p>
        <p><strong>Montant Total :</strong> <?= number_format((float) ($commande['total'] ?? 0), 2) ?> $</p>
    </div>

    <h3>Articles commandés</h3>
    <?php if (empty($lignes)) : ?>
        <p>Aucun article trouvé pour cette commande.</p>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th>Article</th>
                    <th>Prix Unitaire</th>
                    <th>Quantité</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignes as $l) : ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($l['produit_nom'] ?? 'Produit #' . $l['produit_id']) ?></strong></td>
                        <td><?= number_format((float) $l['prix_unitaire'], 2) ?> $</td>
                        <td><?= (int) $l['quantite'] ?></td>
                        <td><?= number_format(((float) $l['prix_unitaire']) * ((int) $l['quantite']), 2) ?> $</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>

