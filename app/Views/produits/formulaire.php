<?php

declare(strict_types=1);

use App\Utils\Csrf;

$produit = $produit ?? null;
$baseUrl = $baseUrl ?? '';
$isEdit = $produit !== null;
$actionUrl = $isEdit 
    ? $baseUrl . '/produits/modifier/' . (string) ($produit['id'] ?? '') 
    : $baseUrl . '/produits/ajouter';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Modifier le produit' : 'Ajouter un produit' ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 650px; margin: 30px auto; padding: 20px; background-color: #f9f9f9; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; font-weight: bold; color: #333; }
        input[type="text"], input[type="number"], textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 14px; }
        textarea { resize: vertical; min-height: 80px; }
        .btn { padding: 10px 18px; border: none; border-radius: 4px; font-size: 15px; cursor: pointer; }
        .btn-primary { background-color: #007bff; color: white; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; color: white; text-decoration: none; display: inline-block; }
        .btn-secondary:hover { background-color: #5a6268; }
        .nav-bar { margin-bottom: 20px; }
        .nav-bar a { color: #007bff; text-decoration: none; font-size: 14px; }
        .image-preview { margin-top: 10px; }
        .image-preview img { max-width: 120px; max-height: 120px; border-radius: 4px; border: 1px solid #ddd; object-fit: cover; }
        .actions { display: flex; justify-content: space-between; align-items: center; margin-top: 25px; }
    </style>
</head>
<body>
    <div class="nav-bar">
        <a href="<?= htmlspecialchars((string) $baseUrl) ?>/produits">← Retour à la liste des produits</a>
    </div>

    <div class="card">
        <h2><?= $isEdit ? 'Modifier le produit' : 'Ajouter un nouveau produit' ?></h2>

        <form method="post" action="<?= htmlspecialchars((string) $actionUrl) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) Csrf::generateToken()) ?>">

            <div class="form-group">
                <label for="nom">Nom du produit :</label>
                <input type="text" name="nom" id="nom" value="<?= htmlspecialchars((string) ($produit['nom'] ?? '')) ?>" required placeholder="Ex: Chaussures de sport">
            </div>

            <div class="form-group">
                <label for="description">Description :</label>
                <textarea name="description" id="description" required placeholder="Description détaillée du produit..."><?= htmlspecialchars((string) ($produit['description'] ?? '')) ?></textarea>
            </div>

            <div class="form-group">
                <label for="prix">Prix ($) :</label>
                <input type="number" name="prix" id="prix" step="0.01" min="0" value="<?= htmlspecialchars((string) ($produit['prix'] ?? '')) ?>" required placeholder="0.00">
            </div>

            <div class="form-group">
                <label for="stock">Quantité en stock :</label>
                <input type="number" name="stock" id="stock" min="0" value="<?= htmlspecialchars((string) ($produit['stock'] ?? '0')) ?>" required placeholder="0">
            </div>

            <div class="form-group">
                <label for="image">URL de l'image :</label>
                <input type="text" name="image" id="image" value="<?= htmlspecialchars((string) ($produit['image_url'] ?? '')) ?>" placeholder="https://example.com/image.jpg ou /uploads/photo.jpg">
                
                <?php if (!empty($produit['image_url'])) : ?>
                    <div class="image-preview">
                        <small>Aperçu actuel :</small><br>
                        <?php 
                            $imgSrc = (string) $produit['image_url'];
                            $fullImgUrl = str_starts_with($imgSrc, 'http') ? $imgSrc : $baseUrl . $imgSrc;
                        ?>
                        <img src="<?= htmlspecialchars((string) $fullImgUrl) ?>" alt="Aperçu du produit">
                    </div>
                <?php endif; ?>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Enregistrer les modifications' : 'Ajouter le produit' ?>
                </button>
                <a href="<?= htmlspecialchars((string) $baseUrl) ?>/produits" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</body>
</html>