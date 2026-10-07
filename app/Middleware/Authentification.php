<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * Class Authentification
 * @package App\Middleware
 */
class Authentification
{
    public function handle(): void
    {
        ini_set('session.use_strict_mode', 1);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $base = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $baseUrl = ($base === '.' || $base === '/' || $base === '\\' || empty($base)) ? '' : '/' . trim(str_replace('\\', '/', $base), '/');

        if (!isset($_SESSION['user'])) {
            header('Location: ' . $baseUrl . '/login');
            exit();
        }

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            session_unset();
            session_destroy();
            header('Location: ' . $baseUrl . '/login');
            exit();
        }

        $_SESSION['last_activity'] = time();
    }

    /**
     * Vérifie si l'utilisateur possède le rôle requis.
     */
    public function checkRole(string $requiredRole): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== $requiredRole) {
            http_response_code(403);
            echo "Accès refusé. Vous n'avez pas la permission d'accéder à cette page.";
            exit();
        }
    }
}

// Alias pour compatibilité
class_alias(Authentification::class, 'App\Middleware\AuthMiddleware');
