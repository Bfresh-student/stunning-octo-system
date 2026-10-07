<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Commande;
use App\Models\LigneCommandes;
use App\Models\Panier;
use App\Utils\AppLogger;
use App\Utils\Csrf;

class CommandeController extends Controller
{
    public function __construct(
        private Commande $modeleCommande,
        private LigneCommandes $modeleLignes,
        private Panier $modelePanier
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

    public function liste(): void
    {
        $baseUrl = $this->getBaseUrl();
        $userId = $this->getUserId();

        if (!$userId) {
            header('Location: ' . $baseUrl . '/login');
            exit;
        }

        $commandes = $this->modeleCommande->getCommandesByUserId($userId);
        $this->render('commandes/liste', [
            'commandes' => $commandes,
            'baseUrl'   => $baseUrl
        ]);
    }

    public function details(int $id): void
    {
        $baseUrl = $this->getBaseUrl();
        $commande = $this->modeleCommande->getCommandeById($id);

        if (!$commande) {
            AppLogger::warning('Consultation de commande introuvable', ['commande_id' => $id]);
            http_response_code(404);
            echo "Commande introuvable.";
            return;
        }

        $lignes = $this->modeleLignes->getLignesByCommandeId($id);
        $this->render('commandes/details', [
            'commande' => $commande,
            'lignes'   => $lignes,
            'baseUrl'  => $baseUrl
        ]);
    }

    public function creer(): void
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
                AppLogger::warning('Tentative de commande : jeton CSRF invalide', ['user_id' => $userId]);
                http_response_code(403);
                echo "Jeton CSRF invalide.";
                return;
            }

            $panier = $this->modelePanier->getPanierByUserId($userId);
            if (empty($panier)) {
                AppLogger::warning('Échec de commande : panier vide', ['user_id' => $userId]);
                header('Location: ' . $baseUrl . '/panier');
                exit;
            }

            $total = $this->modelePanier->getTotal($userId);
            $adresseId = !empty($_POST['adresse_id']) ? (int) $_POST['adresse_id'] : null;

            if ($this->modeleCommande->addCommande($userId, $adresseId, $total, 'en_attente')) {
                // Récupérer la commande qui vient d'être créée
                $commandes = $this->modeleCommande->getCommandesByUserId($userId);
                $commandeId = $commandes[0]['id'] ?? null;

                if ($commandeId) {
                    foreach ($panier as $item) {
                        $this->modeleLignes->addLigneCommande(
                            (int) $commandeId,
                            (int) $item['produit_id'],
                            (int) $item['quantite'],
                            (float) $item['produit_prix']
                        );
                    }
                }

                AppLogger::info('Nouvelle commande passée avec succès', [
                    'commande_id' => $commandeId,
                    'user_id'     => $userId,
                    'total'       => $total,
                    'articles'    => count($panier)
                ]);

                $this->modelePanier->viderPanier($userId);
                header('Location: ' . $baseUrl . '/commandes');
                exit;
            }
        }

        header('Location: ' . $baseUrl . '/panier');
        exit;
    }
}
