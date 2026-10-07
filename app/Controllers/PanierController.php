<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Panier;
use App\Models\Produits;
use App\Utils\AppLogger;
use App\Utils\Csrf;

class PanierController extends Controller
{
    public function __construct(
        private Panier $modelePanier,
        private Produits $modeleProduit
    ) {
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

    private function getUserId(): ?int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user']['id'] ?? null;
    }

    public function index(): void
    {
        $baseUrl = $this->getBaseUrl();
        $userId = $this->getUserId();

        if (!$userId) {
            header('Location: ' . $baseUrl . '/login');
            exit;
        }

        $articles = $this->modelePanier->getPanierByUserId($userId);
        $total = $this->modelePanier->getTotal($userId);

        $this->render('panier/index', [
            'articles' => $articles,
            'total'    => $total,
            'baseUrl'  => $baseUrl
        ]);
    }

    public function ajouter(): void
    {
        $baseUrl = $this->getBaseUrl();
        $userId = $this->getUserId();

        if (!$userId) {
            header('Location: ' . $baseUrl . '/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!Csrf::validateToken($token)) {
                AppLogger::warning('Tentative d\'ajout au panier : jeton CSRF invalide', ['user_id' => $userId]);
                http_response_code(403);
                echo "Jeton CSRF invalide.";
                return;
            }

            $produitId = (int) ($_POST['produit_id'] ?? 0);
            $quantite = max(1, (int) ($_POST['quantite'] ?? 1));

            if ($produitId > 0) {
                $this->modelePanier->ajouterProduit($userId, $produitId, $quantite);
                AppLogger::info('Produit ajouté au panier', [
                    'user_id'    => $userId,
                    'produit_id' => $produitId,
                    'quantite'   => $quantite
                ]);
            }
        }

        header('Location: ' . $baseUrl . '/panier');
        exit;
    }

    public function modifier(): void
    {
        $baseUrl = $this->getBaseUrl();
        $userId = $this->getUserId();

        if (!$userId) {
            header('Location: ' . $baseUrl . '/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!Csrf::validateToken($token)) {
                AppLogger::warning('Tentative de modification panier : jeton CSRF invalide', ['user_id' => $userId]);
                http_response_code(403);
                echo "Jeton CSRF invalide.";
                return;
            }

            $produitId = (int) ($_POST['produit_id'] ?? 0);
            $quantite = (int) ($_POST['quantite'] ?? 0);

            if ($produitId > 0) {
                $this->modelePanier->updateQuantite($userId, $produitId, $quantite);
                AppLogger::info('Quantité modifiée dans le panier', [
                    'user_id'    => $userId,
                    'produit_id' => $produitId,
                    'quantite'   => $quantite
                ]);
            }
        }

        header('Location: ' . $baseUrl . '/panier');
        exit;
    }

    public function supprimer(): void
    {
        $baseUrl = $this->getBaseUrl();
        $userId = $this->getUserId();

        if (!$userId) {
            header('Location: ' . $baseUrl . '/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $produitId = (int) ($_POST['produit_id'] ?? 0);
            if ($produitId > 0) {
                $this->modelePanier->supprimerProduit($userId, $produitId);
                AppLogger::info('Produit retiré du panier', [
                    'user_id'    => $userId,
                    'produit_id' => $produitId
                ]);
            }
        }

        header('Location: ' . $baseUrl . '/panier');
        exit;
    }

    public function vider(): void
    {
        $baseUrl = $this->getBaseUrl();
        $userId = $this->getUserId();

        if ($userId) {
            $this->modelePanier->viderPanier($userId);
            AppLogger::info('Panier vidé', ['user_id' => $userId]);
        }

        header('Location: ' . $baseUrl . '/panier');
        exit;
    }
}
