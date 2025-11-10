<?php

declare(strict_types=1);

namespace Tests\Security\Hashing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Security\Hashing\Argon2IdHasher;
use Omega\Security\Hashing\ArgonHasher;
use Omega\Security\Hashing\BcryptHasher;
use Omega\Security\Hashing\DefaultHasher;

#[CoversClass(Argon2IdHasher::class)]
#[CoversClass(ArgonHasher::class)]
#[CoversClass(BcryptHasher::class)]
#[CoversClass(DefaultHasher::class)]
class HasherTest extends TestCase
{
    /**
     * Test it can hash default hasher.
     *
     * @return void
     */
    public function testItCanHashDefaultHasher(): void
    {
        $hasher = new DefaultHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }

    /**
     * Test it can hash bcrypt hasher.
     *
     * @return void
     */
    public function testItCanHashBcryptHasher(): void
    {
        $hasher = new BcryptHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }

    /**
     * Test it can hash argon hasher.
     *
     * @return void
     */
    public function testItCanHashArgonHasher(): void
    {
        $hasher = new ArgonHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }

    /**
     * Test it can hash argon 2 id hasher.
     *
     * @return void
     */
    public function testItCanHashArgon2IdHasher(): void
    {
        $hasher = new Argon2IdHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }
}
