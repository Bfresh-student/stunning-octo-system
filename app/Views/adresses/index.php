<?php

declare(strict_types=1);

use App\Utils\Csrf;

$adresses = $adresses ?? [];
$baseUrl = $baseUrl ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Adresses</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 8px 12px; background: #28a745; color: white; text-decoration: none; border-radius: 4px; }
        .btn-danger { background: #dc3545; border: none; cursor: pointer; color: white; padding: 6px 10px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f8f9fa; }
        .nav-bar { margin-bottom: 20px; }
        .nav-bar a { margin-right: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="nav-bar">
        <a href="<?= htmlspecialchars($baseUrl . '/profile') ?>">Mon Profil</a>
        <a href="<?= htmlspecialchars($baseUrl . '/produits') ?>">Boutique</a>
    </div>

    <div class="header">
        <h2>Mes Adresses de Livraison</h2>
        <a href="<?= htmlspecialchars($baseUrl . '/adresses/ajouter') ?>" class="btn">+ Ajouter une adresse</a>
    </div>

    <?php if (empty($adresses)) : ?>
        <p>Aucune adresse enregistrée.</p>
    <?php else : ?>
        <table>
            <thead>
                <tr>
                    <th>Rue</th>
                    <th>Ville</th>
                    <th>Code Postal</th>
                    <th>Pays</th>
                    <th>Téléphone</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adresses as $adr) : ?>
                    <tr>
                        <td><?= htmlspecialchars($adr['rue']) ?></td>
                        <td><?= htmlspecialchars($adr['ville']) ?></td>
                        <td><?= htmlspecialchars($adr['code_postal'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($adr['pays']) ?></td>
                        <td><?= htmlspecialchars($adr['telephone'] ?? '-') ?></td>
                        <td>
                            <form method="post" action="<?= htmlspecialchars($baseUrl . '/adresses/supprimer/' . $adr['id']) ?>" onsubmit="return confirm('Supprimer cette adresse ?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generateToken()) ?>">
                                <button type="submit" class="btn-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>

