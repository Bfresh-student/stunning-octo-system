<?php

declare(strict_types=1);

use App\Utils\Csrf;

$baseUrl = $baseUrl ?? '';
$error = $error ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 450px; margin: 40px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="email"], input[type="password"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; cursor: pointer; width: 100%; }
        button:hover { background-color: #0056b3; }
        .error { color: red; margin-bottom: 15px; padding: 10px; background: #ffe6e6; border: 1px solid red; border-radius: 4px; }
        .links { margin-top: 15px; text-align: center; }
    </style>
</head>
<body>
    <h2>Connexion</h2>

    <?php if ($error) : ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= htmlspecialchars($baseUrl . '/login') ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generateToken()) ?>">
        
        <div class="form-group">
            <label for="email">Adresse e-mail :</label>
            <input type="email" name="email" id="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" name="password" id="password" required>
        </div>

        <button type="submit">Se connecter</button>
    </form>

    <div class="links">
        <p>Pas encore de compte ? <a href="<?= htmlspecialchars($baseUrl . '/register') ?>">S'inscrire</a></p>
        <p><a href="<?= htmlspecialchars($baseUrl . '/produits') ?>">Voir les produits</a></p>
    </div>
</body>
</html>

