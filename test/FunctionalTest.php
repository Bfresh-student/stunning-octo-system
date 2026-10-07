<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Routes\Router;
use App\Models\Produits;
use App\Models\User;
use App\Controllers\ProduitsController;
use App\Controllers\UserController;
use App\Controllers\PanierController;
use App\Controllers\CommandeController;
use App\Controllers\AddressesController;

class FunctionalTest extends TestCase
{
    private PDO $pdo;
    private Router $router;

    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        $_POST = [];
        $_GET = [];
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Base de données SQLite en mémoire pour tester tout le flux fonctionnel
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE user (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                mot_de_passe TEXT NOT NULL,
                actif INTEGER NOT NULL DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE produits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom TEXT NOT NULL,
                description TEXT,
                prix REAL NOT NULL,
                stock INTEGER NOT NULL,
                image_url TEXT,
                categorie_id INTEGER
            );

            CREATE TABLE adresses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                rue TEXT NOT NULL,
                ville TEXT NOT NULL,
                code_postal TEXT,
                pays TEXT DEFAULT 'Haïti',
                telephone TEXT
            );

            CREATE TABLE commandes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                adresse_id INTEGER,
                statut TEXT NOT NULL DEFAULT 'en_attente',
                total REAL NOT NULL DEFAULT 0.00,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE lignes_commande (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                commande_id INTEGER NOT NULL,
                produit_id INTEGER NOT NULL,
                quantite INTEGER NOT NULL DEFAULT 1,
                prix_unitaire REAL NOT NULL
            );

            CREATE TABLE panier (
                user_id INTEGER NOT NULL,
                produit_id INTEGER NOT NULL,
                quantite INTEGER NOT NULL DEFAULT 1,
                added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, produit_id)
            );
        ");

        // Insérer un produit de test
        $this->pdo->exec("
            INSERT INTO produits (id, nom, description, prix, stock, image_url) 
            VALUES (1, 'Café Haïtien Test', 'Délicieux café', 12.50, 15, '/uploads/cafe.jpg');
        ");

        $this->router = new Router($this->pdo);

        // Enregistrer les routes de l'application
        $this->router->get('/', [ProduitsController::class, 'liste']);
        $this->router->get('/login', [UserController::class, 'login']);
        $this->router->post('/login', [UserController::class, 'login']);
        $this->router->get('/register', [UserController::class, 'register']);
        $this->router->get('/profile', [UserController::class, 'profile']);
        $this->router->get('/produits', [ProduitsController::class, 'liste']);
        $this->router->get('/produits/ajouter', [ProduitsController::class, 'formulaireAjout']);
        $this->router->post('/produits/ajouter', [ProduitsController::class, 'ajouter']);
        $this->router->get('/produits/modifier/{id}', [ProduitsController::class, 'formulaireModifier']);
        $this->router->get('/panier', [PanierController::class, 'index']);
        $this->router->get('/commandes', [CommandeController::class, 'liste']);
        $this->router->get('/adresses', [AddressesController::class, 'index']);
    }

    public function testGetLoginPageRendersForm(): void
    {
        ob_start();
        $this->router->dispatch('/login', 'GET');
        $html = ob_get_clean();

        $this->assertStringContainsString('Connexion', $html);
        $this->assertStringContainsString('<input type="email" name="email"', $html);
        $this->assertStringContainsString('<input type="password" name="password"', $html);
    }

    public function testGetRegisterPageRendersForm(): void
    {
        ob_start();
        $this->router->dispatch('/register', 'GET');
        $html = ob_get_clean();

        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('name="username"', $html);
        $this->assertStringContainsString('name="confirm_password"', $html);
    }

    public function testGetProduitsRendersCatalogue(): void
    {
        ob_start();
        $this->router->dispatch('/produits', 'GET');
        $html = ob_get_clean();

        $this->assertStringContainsString('Catalogue des Produits', $html);
        $this->assertStringContainsString('Café Haïtien Test', $html);
        $this->assertStringContainsString('12.50 $', $html);
        $this->assertStringContainsString('15 en stock', $html);
    }

    public function testGetProduitAjouterRendersAddForm(): void
    {
        ob_start();
        $this->router->dispatch('/produits/ajouter', 'GET');
        $html = ob_get_clean();

        $this->assertStringContainsString('Ajouter un nouveau produit', $html);
        $this->assertStringContainsString('action="/produits/ajouter"', $html);
    }

    public function testGetProduitModifierRendersEditFormWithExistingData(): void
    {
        ob_start();
        $this->router->dispatch('/produits/modifier/1', 'GET');
        $html = ob_get_clean();

        $this->assertStringContainsString('Modifier le produit', $html);
        $this->assertStringContainsString('action="/produits/modifier/1"', $html);
        $this->assertStringContainsString('value="Café Haïtien Test"', $html);
        $this->assertStringContainsString('value="12.5', $html);
    }

    public function testPostProduitAjouterFailsWithoutCsrfToken(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'nom'   => 'Article Test',
            'prix'  => '20.00',
            'stock' => '5'
        ];

        ob_start();
        $this->router->dispatch('/produits/ajouter', 'POST');
        $output = ob_get_clean();

        $this->assertStringContainsString('CSRF invalide', $output);
    }

    public function testAuthenticatedUserProfileRendersSuccessfully(): void
    {
        $_SESSION['user'] = [
            'id'    => 1,
            'nom'   => 'Alexandre Pétion',
            'email' => 'alexandre@example.com',
            'role'  => 'user'
        ];

        ob_start();
        $this->router->dispatch('/profile', 'GET');
        $html = ob_get_clean();

        $this->assertStringContainsString('Mon Profil', $html);
        $this->assertStringContainsString('Alexandre Pétion', $html);
        $this->assertStringContainsString('alexandre@example.com', $html);
    }

    public function testAuthenticatedCartViewRenders(): void
    {
        $_SESSION['user'] = [
            'id'    => 1,
            'nom'   => 'Jean',
            'email' => 'jean@example.com',
            'role'  => 'user'
        ];

        ob_start();
        $this->router->dispatch('/panier', 'GET');
        $html = ob_get_clean();

        $this->assertStringContainsString('Mon Panier', $html);
    }

    public function testUnknownRouteReturns404(): void
    {
        ob_start();
        $this->router->dispatch('/route-totalement-inconnue', 'GET');
        $output = ob_get_clean();

        $this->assertStringContainsString('Page non trouvée (404)', $output);
    }
}
