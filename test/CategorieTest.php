<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\Categorie;

class CategorieTest extends TestCase
{
    private PDO $pdo;
    private Categorie $categorieModel;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom TEXT NOT NULL UNIQUE,
                description TEXT
            )
        ");

        $this->categorieModel = new Categorie($this->pdo);
    }

    public function testAddCategorieSuccess(): void
    {
        $success = $this->categorieModel->addCategorie("Électronique", "Appareils et gadgets");

        $this->assertTrue($success);

        $cat = $this->categorieModel->getCategorieByNom("Électronique");
        $this->assertNotNull($cat);
        $this->assertSame("Appareils et gadgets", $cat['description']);
    }

    public function testAddCategorieFailsWithEmptyName(): void
    {
        $success = $this->categorieModel->addCategorie("   ");

        $this->assertFalse($success);
    }

    public function testGetCategoriesReturnsAll(): void
    {
        $this->categorieModel->addCategorie("Vêtements");
        $this->categorieModel->addCategorie("Alimentation");

        $categories = $this->categorieModel->getCategories();

        $this->assertCount(2, $categories);
    }

    public function testUpdateCategorie(): void
    {
        $this->categorieModel->addCategorie("Livres", "Ancienne description");
        $cat = $this->categorieModel->getCategorieByNom("Livres");

        $success = $this->categorieModel->updateCategorie((int) $cat['id'], "Livres & BD", "Nouvelle description");

        $this->assertTrue($success);
        $updated = $this->categorieModel->getCategorieById((int) $cat['id']);
        $this->assertSame("Livres & BD", $updated['nom']);
        $this->assertSame("Nouvelle description", $updated['description']);
    }

    public function testDeleteCategorie(): void
    {
        $this->categorieModel->addCategorie("Bricolage");
        $cat = $this->categorieModel->getCategorieByNom("Bricolage");

        $success = $this->categorieModel->deleteCategorie((int) $cat['id']);

        $this->assertTrue($success);
        $this->assertNull($this->categorieModel->getCategorieById((int) $cat['id']));
    }
}

