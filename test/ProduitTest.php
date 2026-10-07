<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\Produits;

class ProduitTest extends TestCase
{
    private PDO $pdo;
    private Produits $produitModel;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE produits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom TEXT NOT NULL,
                description TEXT,
                prix REAL NOT NULL,
                stock INTEGER NOT NULL,
                image_url TEXT,
                categorie_id INTEGER
            )
        ");

        $this->produitModel = new Produits($this->pdo);
    }

    public function testAddProduitSuccess(): void
    {
        $success = $this->produitModel->addProduit("Café Haïtien", "Café 100% Arabica", 15.50, 50, "/uploads/cafe.jpg");

        $this->assertTrue($success);

        $produits = $this->produitModel->getProduits();
        $this->assertCount(1, $produits);
        $this->assertSame("Café Haïtien", $produits[0]['nom']);
        $this->assertEquals(15.50, (float) $produits[0]['prix']);
        $this->assertSame(50, (int) $produits[0]['stock']);
    }

    public function testAddProduitFailsWithInvalidPrice(): void
    {
        $success = $this->produitModel->addProduit("Produit Invalide", "Desc", -5.0, 10, "/uploads/test.jpg");

        $this->assertFalse($success);
    }

    public function testAddProduitFailsWithNegativeStock(): void
    {
        $success = $this->produitModel->addProduit("Produit Invalide", "Desc", 10.0, -1, "/uploads/test.jpg");

        $this->assertFalse($success);
    }

    public function testUpdateProduit(): void
    {
        $this->produitModel->addProduit("Ancien Produit", "Desc", 20.0, 5, "/uploads/p.jpg");
        $produits = $this->produitModel->getProduits();
        $id = (int) $produits[0]['id'];

        $success = $this->produitModel->updateProduit($id, "Produit Modifié", "Nouvelle desc", 25.0, 15, "/uploads/new.jpg");

        $this->assertTrue($success);
        $p = $this->produitModel->getProduitById($id);
        $this->assertSame("Produit Modifié", $p['nom']);
        $this->assertEquals(25.0, (float) $p['prix']);
        $this->assertSame(15, (int) $p['stock']);
    }

    public function testDeleteProduit(): void
    {
        $this->produitModel->addProduit("À supprimer", "Desc", 10.0, 1, "/uploads/del.jpg");
        $produits = $this->produitModel->getProduits();
        $id = (int) $produits[0]['id'];

        $success = $this->produitModel->deleteProduit($id);

        $this->assertTrue($success);
        $this->assertNull($this->produitModel->getProduitById($id));
    }
}

