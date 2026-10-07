<?php

declare(strict_types=1);

namespace Test;

use App\Utils\AppLogger;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class AppLoggerTest extends TestCase
{
    private ?Logger $originalLogger = null;

    protected function setUp(): void
    {
        // Sauvegarde de l'instance courante pour ne pas affecter les autres tests
        $this->originalLogger = AppLogger::getLogger();
    }

    protected function tearDown(): void
    {
        // Restauration de l'instance d'origine
        AppLogger::setLogger($this->originalLogger);
    }

    public function testGetLoggerReturnsMonologLogger(): void
    {
        $logger = AppLogger::getLogger();
        $this->assertInstanceOf(Logger::class, $logger);
        $this->assertSame('mon_application', $logger->getName());
    }

    public function testLoggingLevelsAndContext(): void
    {
        $testHandler = new TestHandler(Level::Debug);
        $customLogger = new Logger('test_channel');
        $customLogger->pushHandler($testHandler);

        AppLogger::setLogger($customLogger);

        // Test info log (comme demandé : 'Nouvel utilisateur inscrit')
        AppLogger::info('Nouvel utilisateur inscrit', ['id' => 42]);
        $this->assertTrue($testHandler->hasInfoThatContains('Nouvel utilisateur inscrit'));
        $records = $testHandler->getRecords();
        $this->assertSame(['id' => 42], $records[0]->context);

        // Test error log (comme demandé : 'Échec de connexion')
        AppLogger::error('Échec de connexion', ['email' => 'test@mail.com']);
        $this->assertTrue($testHandler->hasErrorThatContains('Échec de connexion'));

        // Test warning log
        AppLogger::warning('Tentative non autorisée', ['ip' => '127.0.0.1']);
        $this->assertTrue($testHandler->hasWarningThatContains('Tentative non autorisée'));

        // Test critical log
        AppLogger::critical('Panne de base de données');
        $this->assertTrue($testHandler->hasCriticalThatContains('Panne de base de données'));
    }

    public function testFileLogging(): void
    {
        $tempLog = sys_get_temp_dir() . '/test_monolog_' . uniqid() . '.log';

        $logger = new Logger('temp_test');
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($tempLog, Level::Debug));
        AppLogger::setLogger($logger);

        AppLogger::info('Test écriture fichier', ['key' => 'value']);

        $this->assertFileExists($tempLog);
        $content = file_get_contents($tempLog);
        $this->assertStringContainsString('Test écriture fichier', $content);
        $this->assertStringContainsString('value', $content);

        // Nettoyage du fichier temporaire
        if (file_exists($tempLog)) {
            unlink($tempLog);
        }
    }
}
