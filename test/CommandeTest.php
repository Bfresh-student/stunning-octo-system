<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\Commande;
use App\Models\LigneCommandes;

class CommandeTest extends TestCase
{
    private PDO $pdo;
    private Commande $commandeModel;
    private LigneCommandes $ligneModel;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
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
            CREATE TABLE produits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom TEXT NOT NULL
            );
        ");

        $this->commandeModel = new Commande($this->pdo);
        $this->ligneModel = new LigneCommandes($this->pdo);
    }

    public function testAddCommandeSuccess(): void
    {
        $success = $this->commandeModel->addCommande(1, null, 150.00, 'en_attente');

        $this->assertTrue($success);

        $commandes = $this->commandeModel->getCommandesByUserId(1);
        $this->assertCount(1, $commandes);
        $this->assertEquals(150.00, (float) $commandes[0]['total']);
        $this->assertSame('en_attente', $commandes[0]['statut']);
    }

    public function testAddCommandeFailsWithInvalidStatus(): void
    {
        $success = $this->commandeModel->addCommande(1, null, 100.00, 'statut_inconnu');

        $this->assertFalse($success);
    }

    public function testUpdateStatut(): void
    {
        $this->commandeModel->addCommande(1, null, 50.00, 'en_attente');
        $cmd = $this->commandeModel->getCommandesByUserId(1)[0];

        $success = $this->commandeModel->updateStatut((int) $cmd['id'], 'payee');

        $this->assertTrue($success);
        $updated = $this->commandeModel->getCommandeById((int) $cmd['id']);
        $this->assertSame('payee', $updated['statut']);
    }

    public function testAddLigneCommandeAndFetch(): void
    {
        $this->commandeModel->addCommande(1, null, 100.00);
        $cmdId = (int) $this->commandeModel->getCommandesByUserId(1)[0]['id'];

        $success = $this->ligneModel->addLigneCommande($cmdId, 10, 2, 50.00);

        $this->assertTrue($success);
        $lignes = $this->ligneModel->getLignesByCommandeId($cmdId);
        $this->assertCount(1, $lignes);
        $this->assertSame(2, (int) $lignes[0]['quantite']);
        $this->assertEquals(50.00, (float) $lignes[0]['prix_unitaire']);
    }

    public function testDeleteCommande(): void
    {
        $this->commandeModel->addCommande(1, null, 20.00);
        $cmdId = (int) $this->commandeModel->getCommandesByUserId(1)[0]['id'];

        $success = $this->commandeModel->deleteCommande($cmdId);

        $this->assertTrue($success);
        $this->assertNull($this->commandeModel->getCommandeById($cmdId));
    }
}
