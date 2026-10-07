<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Services\Hashage;

class HashageTest extends TestCase
{
    private Hashage $hashage;

    protected function setUp(): void
    {
        $this->hashage = new Hashage();
    }

    public function testHashGeneratesValidHash(): void
    {
        $password = "MonMotDePasse123!";
        $hash = $this->hashage->hash($password);

        $this->assertNotEmpty($hash);
        $this->assertNotSame($password, $hash);
        $this->assertTrue(password_get_info($hash)['algo'] !== null);
    }

    public function testVerifyReturnsTrueForCorrectPassword(): void
    {
        $password = "SecretPassword123";
        $hash = $this->hashage->hash($password);

        $this->assertTrue($this->hashage->verify($password, $hash));
    }

    public function testVerifyReturnsFalseForIncorrectPassword(): void
    {
        $password = "SecretPassword123";
        $hash = $this->hashage->hash($password);

        $this->assertFalse($this->hashage->verify("WrongPassword", $hash));
    }

    public function testHashesAreUniqueForSamePassword(): void
    {
        $password = "IdenticalPassword";
        $hash1 = $this->hashage->hash($password);
        $hash2 = $this->hashage->hash($password);

        $this->assertNotSame($hash1, $hash2);
        $this->assertTrue($this->hashage->verify($password, $hash1));
        $this->assertTrue($this->hashage->verify($password, $hash2));
    }
}

