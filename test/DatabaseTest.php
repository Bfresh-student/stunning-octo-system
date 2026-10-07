<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\config\MysqlDatabase;

class DatabaseTest extends TestCase
{
    private MysqlDatabase $database;

    protected function setUp(): void
    {
        $this->database = new MysqlDatabase();
    }

    public function testConnection(): void
    {
        $pdo = $this->database->getPdo();
        $this->assertInstanceOf(PDO::class, $pdo);
    }
}

?>