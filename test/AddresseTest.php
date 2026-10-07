<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\Addresse;

class AddresseTest extends TestCase
{
    private PDO $pdo;
    private Addresse $adresseModel;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE adresses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                rue TEXT NOT NULL,
                ville TEXT NOT NULL,
                code_postal TEXT,
                pays TEXT DEFAULT 'Haïti',
                telephone TEXT
            )
        ");

        $this->adresseModel = new Addresse($this->pdo);
    }

    public function testAddAdresseSuccess(): void
    {
        $success = $this->adresseModel->addAdresse(
            1,
            "12 Rue Capois",
            "Port-au-Prince",
            "HT6110",
            "Haïti",
            "+509 3700 0000"
        );

        $this->assertTrue($success);

        $adresses = $this->adresseModel->getAdressesByUserId(1);
        $this->assertCount(1, $adresses);
        $this->assertSame("12 Rue Capois", $adresses[0]['rue']);
        $this->assertSame("Port-au-Prince", $adresses[0]['ville']);
    }

    public function testAddAdresseFailsWithEmptyStreetOrCity(): void
    {
        $fail1 = $this->adresseModel->addAdresse(1, "", "Port-au-Prince");
        $fail2 = $this->adresseModel->addAdresse(1, "Rue Capois", "");

        $this->assertFalse($fail1);
        $this->assertFalse($fail2);
    }

    public function testUpdateAdresse(): void
    {
        $this->adresseModel->addAdresse(1, "Ancienne Rue", "Pétion-Ville");
        $adresse = $this->adresseModel->getAdressesByUserId(1)[0];

        $success = $this->adresseModel->updateAdresse(
            (int) $adresse['id'],
            "Nouvelle Rue",
            "Delmas",
            "HT6120",
            "Haïti",
            "+509 4444 0000"
        );

        $this->assertTrue($success);
        $updated = $this->adresseModel->getAdresseById((int) $adresse['id']);
        $this->assertSame("Nouvelle Rue", $updated['rue']);
        $this->assertSame("Delmas", $updated['ville']);
    }

    public function testDeleteAdresse(): void
    {
        $this->adresseModel->addAdresse(1, "Rue à supprimer", "Gonaïves");
        $adresse = $this->adresseModel->getAdressesByUserId(1)[0];

        $success = $this->adresseModel->deleteAdresse((int) $adresse['id']);

        $this->assertTrue($success);
        $this->assertNull($this->adresseModel->getAdresseById((int) $adresse['id']));
    }
}

