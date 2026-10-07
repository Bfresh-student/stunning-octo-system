<?php

declare(strict_types=1);

namespace App\config;

use App\Utils\AppLogger;
use PDO;
use Dotenv\Dotenv;

// Chargement des variables d'environnement
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

// Connexion à la base de données
class MysqlDatabase {
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;
    private PDO $pdo;

    public function __construct()
    {
        $this->host = $_ENV['DB_HOST'] ?? (getenv('DB_HOST') ?: 'localhost');
        $this->db_name = $_ENV['DB_NAME'] ?? (getenv('DB_NAME') ?: '');
        $this->username = $_ENV['DB_USER'] ?? (getenv('DB_USER') ?: 'root');
        $this->password = $_ENV['DB_PASSWORD'] ?? (getenv('DB_PASSWORD') !== false ? (string) getenv('DB_PASSWORD') : '');
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            AppLogger::critical('Erreur de connexion à la base de données', [
                'host'    => $this->host,
                'db_name' => $this->db_name,
                'error'   => $e->getMessage(),
                'code'    => (int) $e->getCode()
            ]);
            error_log("Connection failed: " . $e->getMessage() . " Code: " . $e->getCode());
            throw new \PDOException("Erreur de connexion à la base de données : " . $e->getMessage(), (int) $e->getCode());
        }
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}