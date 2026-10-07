<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\config\MysqlDatabase;
use App\Routes\Router;
use App\Controllers\UserController;
use App\Controllers\ProduitsController;
use App\Controllers\PanierController;
use App\Controllers\CommandeController;
use App\Controllers\AddressesController;

try {
    $db = new MysqlDatabase();
    $pdo = $db->getPdo();
} catch (\Throwable $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$router = new Router($pdo);

// --- Redirection racine ---
$router->get('/', [ProduitsController::class, 'liste']);

// --- Authentification & Utilisateurs ---
$router->get('/login', [UserController::class, 'login']);
$router->post('/login', [UserController::class, 'login']);
$router->get('/register', [UserController::class, 'register']);
$router->post('/register', [UserController::class, 'register']);
$router->get('/logout', [UserController::class, 'logout']);
$router->get('/profile', [UserController::class, 'profile']);

// --- Catalogue Produits ---
$router->get('/produits', [ProduitsController::class, 'liste']);
$router->get('/produits/ajouter', [ProduitsController::class, 'formulaireAjout']);
$router->post('/produits/ajouter', [ProduitsController::class, 'ajouter']);
$router->get('/produits/modifier/{id}', [ProduitsController::class, 'formulaireModifier']);
$router->post('/produits/modifier/{id}', [ProduitsController::class, 'modifier']);
$router->post('/produits/supprimer/{id}', [ProduitsController::class, 'supprimer']);

// --- Panier ---
$router->get('/panier', [PanierController::class, 'index']);
$router->post('/panier/ajouter', [PanierController::class, 'ajouter']);
$router->post('/panier/modifier', [PanierController::class, 'modifier']);
$router->post('/panier/supprimer', [PanierController::class, 'supprimer']);
$router->post('/panier/vider', [PanierController::class, 'vider']);

// --- Commandes ---
$router->get('/commandes', [CommandeController::class, 'liste']);
$router->post('/commandes/creer', [CommandeController::class, 'creer']);
$router->get('/commandes/{id}', [CommandeController::class, 'details']);

// --- Adresses ---
$router->get('/adresses', [AddressesController::class, 'index']);
$router->get('/adresses/ajouter', [AddressesController::class, 'ajouter']);
$router->post('/adresses/ajouter', [AddressesController::class, 'ajouter']);
$router->post('/adresses/supprimer/{id}', [AddressesController::class, 'supprimer']);

// Dispatcher la requête
$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');

