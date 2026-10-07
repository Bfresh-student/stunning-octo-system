<?php

declare(strict_types=1);

use App\Utils\Csrf;

 $produit = $produit ?? null;
    $baseUrl = $baseUrl ?? '';

?>
<form  method="post" action="<?=htmlspecialchars($baseUrl . '/register')?>" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Csrf::generateToken()) ?>">
    <div>
        <label for="username">Nom d'utilisateur:</label>
        <input type="text" name="username" id="username" required value="<?= htmlspecialchars($username ?? '') ?>">
    </div>
    <div>
        <label for="email">Adresse e-mail:</label>
        <input type="email" name="email" id="email" required value="<?= htmlspecialchars($email ?? '') ?>">
    </div>
    <div>
        <label for="profile_image">Image de profil:</label>
        <input type="file" name="profile_image" id="profile_image" accept=".jpg, .jpeg, .png">
    </div>
    <div>
        <label for="password">Mot de passe:</label>
        <input type="password" name="password" id="password" required>
    </div>
    <div>
        <label for="confirm_password">Confirmer le mot de passe:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
    </div>
    <button type="submit">S'inscrire</button>
</form>