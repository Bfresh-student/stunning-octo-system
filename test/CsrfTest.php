<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Utils\Csrf as CsrfUtil;
use App\Middleware\Csrf as CsrfMiddleware;

class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['csrf_token']);
    }

    public function testGenerateTokenCreatesHexToken(): void
    {
        $token = CsrfUtil::generateToken();

        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token)); // bin2hex(random_bytes(32)) = 64 caractères hex
        $this->assertSame($token, $_SESSION['csrf_token']);
    }

    public function testGenerateTokenReusesExistingToken(): void
    {
        $token1 = CsrfUtil::generateToken();
        $token2 = CsrfUtil::generateToken();

        $this->assertSame($token1, $token2);
    }

    public function testValidateTokenReturnsTrueForValidToken(): void
    {
        $token = CsrfUtil::generateToken();

        $this->assertTrue(CsrfUtil::validateToken($token));
    }

    public function testValidateTokenReturnsFalseForInvalidToken(): void
    {
        CsrfUtil::generateToken();

        $this->assertFalse(CsrfUtil::validateToken('mauvais_jeton_csrf'));
        $this->assertFalse(CsrfUtil::validateToken(''));
    }

    public function testCsrfMiddlewareVerifyToken(): void
    {
        $token = CsrfUtil::generateToken();
        $middleware = new CsrfMiddleware();

        $this->assertTrue($middleware->verifyToken($token));
        $this->assertFalse($middleware->verifyToken('invalide'));
    }
}

