<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Controller;
use App\Models\Addresse;
use App\Utils\Csrf;

class AddressesController extends Controller
{
    public function __construct(private Addresse $modeleAdresse)
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

        $adresses = $this->modeleAdresse->getAdressesByUserId($userId);
        $this->render('adresses/index', [
            'adresses' => $adresses,
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
                http_response_code(403);
                echo "Jeton CSRF invalide.";
                return;
            }

            $rue = trim($_POST['rue'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $codePostal = trim($_POST['code_postal'] ?? '') ?: null;
            $pays = trim($_POST['pays'] ?? 'Haïti') ?: 'Haïti';
            $telephone = trim($_POST['telephone'] ?? '') ?: null;

            $this->modeleAdresse->addAdresse($userId, $rue, $ville, $codePostal, $pays, $telephone);
            header('Location: ' . $baseUrl . '/adresses');
            exit;
        }

        $this->render('adresses/formulaire', [
            'adresse' => null,
            'baseUrl' => $baseUrl
        ]);
    }

    public function supprimer(int $id): void
    {
        $baseUrl = $this->getBaseUrl();
        $this->modeleAdresse->deleteAdresse($id);
        header('Location: ' . $baseUrl . '/adresses');
        exit;
    }
}
