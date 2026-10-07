<?php

declare(strict_types=1);

namespace App\Utils;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

class AppLogger
{
    private static ?Logger $logger = null;

    /**
     * Récupère l'instance unique du Logger Monolog.
     */
    public static function getLogger(): Logger
    {
        if (self::$logger === null) {
            self::initLogger();
        }

        return self::$logger;
    }

    /**
     * Permet d'injecter ou de réinitialiser l'instance du Logger (utile pour les tests).
     */
    public static function setLogger(?Logger $logger): void
    {
        self::$logger = $logger;
    }

    /**
     * Initialise le logger avec le fichier de logs et le niveau approprié.
     */
    private static function initLogger(): void
    {
        $channel = $_ENV['LOG_CHANNEL'] ?? (getenv('LOG_CHANNEL') ?: 'mon_application');
        $defaultLogPath = dirname(__DIR__, 2) . '/logs/app.log';
        $logPath = $_ENV['LOG_FILE'] ?? (getenv('LOG_FILE') ?: $defaultLogPath);

        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $levelStr = strtoupper($_ENV['LOG_LEVEL'] ?? (getenv('LOG_LEVEL') ?: 'DEBUG'));
        $level = match ($levelStr) {
            'INFO'      => Level::Info,
            'NOTICE'    => Level::Notice,
            'WARNING'   => Level::Warning,
            'ERROR'     => Level::Error,
            'CRITICAL'  => Level::Critical,
            'ALERT'     => Level::Alert,
            'EMERGENCY' => Level::Emergency,
            default     => Level::Debug,
        };

        $logger = new Logger($channel);
        $logger->pushHandler(new StreamHandler($logPath, $level));

        self::$logger = $logger;
    }

    public static function debug(string $message, array $context = []): void
    {
        self::getLogger()->debug($message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::getLogger()->info($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::getLogger()->warning($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::getLogger()->error($message, $context);
    }

    public static function critical(string $message, array $context = []): void
    {
        self::getLogger()->critical($message, $context);
    }
}
