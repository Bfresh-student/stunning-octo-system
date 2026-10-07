<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Produits;
use App\Utils\Csrf;

class ProduitsController extends Controller
{
    public function __construct(private Produits $modeleProduit)
    {
    }

    private function getBaseUrl(): string
    {
        $base = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($base === '.' || $base === '/' || $base === '\\' || empty($base)) {
            return '';
        }
        $base = str_replace('\\', '/', $base);
        return '/' . trim($base, '/');
    }

    public function liste(): void
    {
        $baseUrl = $this->getBaseUrl();
        $this->render('produits/liste', [
            'produits' => $this->modeleProduit->getProduits(),
            'baseUrl'  => $baseUrl
        ]);
    }

    public function formulaireAjout(): void
    {
        $baseUrl = $this->getBaseUrl();
        $this->render('produits/formulaire', [
            'produit' => null,
            'baseUrl' => $baseUrl
        ]);
    }

    public function ajouter(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // Vérification du jeton CSRF
            $token = $_POST['csrf_token'] ?? '';
            if (!Csrf::validateToken($token)) {
                http_response_code(403);
                echo "Jeton CSRF invalide.";
                return;
            }

            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $prix = (float) ($_POST['prix'] ?? 0);
            $stock = (int) ($_POST['stock'] ?? 0);
            $image = trim($_POST['image'] ?? '');

            $this->modeleProduit->addProduit($nom, $description, $prix, $stock, $image);
            header('Location: ' . $this->getBaseUrl() . '/produits');
            exit;
        }

        $this->formulaireAjout();
    }

    public function formulaireModifier(int $id): void
    {
        $produit = $this->modeleProduit->getProduitById($id);
        if (!$produit) {
            http_response_code(404);
            echo "Produit introuvable";
            return;
        }
        $baseUrl = $this->getBaseUrl();
        $this->render('produits/formulaire', [
            'produit' => $produit,
            'baseUrl' => $baseUrl
        ]);
    }

    public function modifier(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $token = $_POST['csrf_token'] ?? '';
            if (!Csrf::validateToken($token)) {
                http_response_code(403);
                echo "Jeton CSRF invalide.";
                return;
            }

            $nom = trim($_POST['nom'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $prix = (float) ($_POST['prix'] ?? 0);
            $stock = (int) ($_POST['stock'] ?? 0);
            $image = trim($_POST['image'] ?? '');

            $this->modeleProduit->updateProduit($id, $nom, $description, $prix, $stock, $image);
            header('Location: ' . $this->getBaseUrl() . '/produits');
            exit;
        }

        $this->formulaireModifier($id);
    }

    public function supprimer(int $id): void
    {
        $this->modeleProduit->deleteProduit($id);
        header('Location: ' . $this->getBaseUrl() . '/produits');
        exit;
    }
}
