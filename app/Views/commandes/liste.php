<?php

declare(strict_types=1);

$commandes = $commandes ?? [];
$baseUrl = $baseUrl ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-en_attente { background: #ffeeba; color: #856404; }
        .badge-payee { background: #c3e6cb; color: #155724; }
        .badge-expediee { background: #bee5eb; color: #0c5460; }
        .badge-livree { background: #d4edda; color: #155724; }
        .badge-annulee { background: #f8d7da; color: #721c24; }
        .nav-bar { margin-bottom: 20px; }
        .nav-bar a { margin-right: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="nav-bar">
        <a href="<?= htmlspecialchars($baseUrl . '/profile') ?>">Mon Profil</a>
        <a href="<?= htmlspecialchars($baseUrl . '/produits') ?>">Boutique</a>
        <a href="<?= htmlspecialchars($baseUrl . '/panier') ?>">Mon Panier</a>
    </div>

    <h2>Historique de mes Commandes</h2>

    <?php if (empty($commandes)) : ?>
        <p>Vous n'avez passé aucune commande pour l'instant.</p>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th>N° Commande</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $cmd) : ?>
                    <tr>
                        <td>#<?= (int) $cmd['id'] ?></td>
                        <td><?= htmlspecialchars($cmd['created_at']) ?></td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($cmd['statut']) ?>">
                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $cmd['statut']))) ?>
                            </span>
                        </td>
                        <td><?= number_format((float) $cmd['total'], 2) ?> $</td>
                        <td>
                            <a href="<?= htmlspecialchars($baseUrl . '/commandes/' . $cmd['id']) ?>">Voir détails</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>

