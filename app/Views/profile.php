<?php

declare(strict_types=1);

$user = $user ?? null;
$baseUrl = $baseUrl ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; }
        .card { border: 1px solid #ddd; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav-links { margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
        .nav-links a { display: inline-block; padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .nav-links a.logout { background: #dc3545; }
        .profile-img { max-width: 120px; border-radius: 50%; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Mon Profil</h2>

        <?php if (!empty($user['profile_image'])) : ?>
            <img src="<?= htmlspecialchars($baseUrl . $user['profile_image']) ?>" alt="Photo de profil" class="profile-img">
        <?php endif; ?>

        <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom'] ?? 'Non renseigné') ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
        <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role'] ?? 'Utilisateur') ?></p>

        <div class="nav-links">
            <a href="<?= htmlspecialchars($baseUrl . '/produits') ?>">Boutique / Produits</a>
            <a href="<?= htmlspecialchars($baseUrl . '/panier') ?>">Mon Panier</a>
            <a href="<?= htmlspecialchars($baseUrl . '/commandes') ?>">Mes Commandes</a>
            <a href="<?= htmlspecialchars($baseUrl . '/adresses') ?>">Mes Adresses</a>
            <a href="<?= htmlspecialchars($baseUrl . '/logout') ?>" class="logout">Déconnexion</a>
        </div>
    </div>
</body>
</html>