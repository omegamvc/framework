<?php

declare(strict_types=1);

namespace Tests\Security;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Security\Algo;
use Omega\Security\Crypt;

#[CoversClass(Algo::class)]
#[CoversClass(Crypt::class)]
class CryptTest extends TestCase
{
    private Crypt $crypt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->crypt = new Crypt('3sc3RLrpd17', Algo::AES_256_CBC);
    }

    /**
     * Test it can encrypt decrypt correctly.
     *
     * @return void
     */
    public function testItCanEncryptDecryptCorrectly(): void
    {
        $planText  = 'My secret message 1234';
        $encrypted = $this->crypt->encrypt($planText);
        $decrypted = $this->crypt->decrypt($encrypted);

        $this->assertEquals($planText, $decrypted);
    }

    /**
     * Test it can encrypt correctly with custom pass phrase.
     *
     * @return void
     */
    public function testItCanEncryptCorrectlyWithCustomPassphrase(): void
    {
        $planText  = 'My secret message 1234';
        $encrypted = $this->crypt->encrypt($planText, 'secret');
        $decrypted = $this->crypt->decrypt($encrypted, 'secret');

        $this->assertEquals('My secret message 1234', $decrypted);
    }
}
