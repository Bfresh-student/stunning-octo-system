<?php

declare(strict_types=1);

namespace App\Middleware;

/**
 * Class Csrf
 * @package App\Middleware
 */
class Csrf
{
    /**
     * Generate a CSRF token and store it in the session.
     *
     * @return bool True if the token is valid, false otherwise.
     */
    public function verifyToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
