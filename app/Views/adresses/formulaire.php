<?php

declare(strict_types=1);

use App\Utils\Csrf;

$baseUrl = $baseUrl ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Adresse</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 30px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #28a745; color: white; padding: 10px 15px; border: none; cursor: pointer; width: 100%; }
        .nav-bar { margin-bottom: 20px; }
        .nav-bar a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="nav-bar">
        <a href="<?= htmlspecialchars($baseUrl . '/adresses') ?>">← Retour aux adresses</a>
    </div>

    <h2>Nouvelle Adresse</h2>

    <form method="post" action="<?= htmlspecialchars($baseUrl . '/adresses/ajouter') ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generateToken()) ?>">

        <div class="form-group">
            <label for="rue">Rue / Adresse :</label>
            <input type="text" name="rue" id="rue" required>
        </div>

        <div class="form-group">
            <label for="ville">Ville :</label>
            <input type="text" name="ville" id="ville" required>
        </div>

        <div class="form-group">
            <label for="code_postal">Code Postal :</label>
            <input type="text" name="code_postal" id="code_postal">
        </div>

        <div class="form-group">
            <label for="pays">Pays :</label>
            <input type="text" name="pays" id="pays" value="Haïti" required>
        </div>

        <div class="form-group">
            <label for="telephone">Téléphone :</label>
            <input type="text" name="telephone" id="telephone">
        </div>

        <button type="submit">Enregistrer l'adresse</button>
    </form>
</body>
</html>

