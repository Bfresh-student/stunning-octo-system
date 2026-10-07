<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    private PDO $pdo;
    private User $userModel;

    protected function setUp(): void
    {
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
            )
        ");

        $this->userModel = new User($this->pdo);
    }

    public function testAddUserSuccess(): void
    {
        $result = $this->userModel->addUser("Jean Dupont", "jean@example.com", "password123");

        $this->assertTrue($result);

        $user = $this->userModel->getUserByEmail("jean@example.com");
        $this->assertNotNull($user);
        $this->assertSame("Jean Dupont", $user['nom']);
        $this->assertSame("jean@example.com", $user['email']);
    }

    public function testAddUserFailsWithInvalidEmail(): void
    {
        $result = $this->userModel->addUser("Jean Dupont", "email_invalide", "password123");

        $this->assertFalse($result);
    }

    public function testAddUserFailsWithShortPassword(): void
    {
        $result = $this->userModel->addUser("Jean Dupont", "jean@example.com", "short");

        $this->assertFalse($result);
    }

    public function testAddUserFailsWithEmptyName(): void
    {
        $result = $this->userModel->addUser("   ", "jean@example.com", "password123");

        $this->assertFalse($result);
    }

    public function testGetUserByEmailReturnsNullWhenNotFound(): void
    {
        $user = $this->userModel->getUserByEmail("inexistant@example.com");

        $this->assertNull($user);
    }

    public function testChangeName(): void
    {
        $this->userModel->addUser("Ancien Nom", "nom@example.com", "password123");
        $user = $this->userModel->getUserByEmail("nom@example.com");

        $success = $this->userModel->changeName((int) $user['id'], "Nouveau Nom");

        $this->assertTrue($success);
        $updated = $this->userModel->getUserById((int) $user['id']);
        $this->assertSame("Nouveau Nom", $updated['nom']);
    }

    public function testChangePassword(): void
    {
        $this->userModel->addUser("User Pass", "pass@example.com", "password123");
        $user = $this->userModel->getUserByEmail("pass@example.com");

        $success = $this->userModel->changePassword((int) $user['id'], "nouveau_hash_secret");

        $this->assertTrue($success);
        $updated = $this->userModel->getUserById((int) $user['id']);
        $this->assertSame("nouveau_hash_secret", $updated['mot_de_passe']);
    }

    public function testDeleteUser(): void
    {
        $this->userModel->addUser("A Supprimer", "del@example.com", "password123");
        $user = $this->userModel->getUserByEmail("del@example.com");

        $success = $this->userModel->deleteUser((int) $user['id']);

        $this->assertTrue($success);
        $this->assertNull($this->userModel->getUserById((int) $user['id']));
    }
}

