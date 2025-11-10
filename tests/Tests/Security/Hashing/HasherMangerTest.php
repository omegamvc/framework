<?php

declare(strict_types=1);

namespace Tests\Security\Hashing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Security\Hashing\BcryptHasher;
use Omega\Security\Hashing\HashManager;

#[CoversClass(BcryptHasher::class)]
#[CoversClass(HashManager::class)]
class HasherMangerTest extends TestCase
{
    /**
     * Test it can hash default hasher.
     *
     * @return void
     */
    public function testItCanHashDefaultHasher(): void
    {
        $hasher = new HashManager();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }

    /**
     * Test it can use driver.
     *
     * @return void
     */
    public function testItCanUsingDriver(): void
    {
        $hasher = new HashManager();
        $hasher->setDriver('bcrypt', new BcryptHasher());
        $hash   = $hasher->driver('bcrypt')->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->driver('bcrypt')->verify('password', $hash));
        $this->assertTrue($hasher->driver('bcrypt')->isValidAlgorithm($hash));
    }
}
