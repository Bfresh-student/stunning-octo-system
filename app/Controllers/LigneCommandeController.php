<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\LigneCommandes;
use App\Utils\Csrf;

class LigneCommandeController extends Controller
{
    public function __construct(private LigneCommandes $modeleLignes)
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

    public function modifier(int $id): void
    {
        $baseUrl = $this->getBaseUrl();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!Csrf::validateToken($token)) {
                http_response_code(403);
                echo "Jeton CSRF invalide.";
                return;
            }

            $quantite = (int) ($_POST['quantite'] ?? 1);
            $this->modeleLignes->updateQuantite($id, $quantite);
        }

        header('Location: ' . ($baseUrl ?: '/'));
        exit;
    }

    public function supprimer(int $id): void
    {
        $baseUrl = $this->getBaseUrl();
        $this->modeleLignes->deleteLigneCommande($id);
        header('Location: ' . ($baseUrl ?: '/'));
        exit;
    }
}
