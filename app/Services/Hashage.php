<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Class Hashage
 * @package App\Services
 */
class Hashage
{
    public function __construct()
    {
    }
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
