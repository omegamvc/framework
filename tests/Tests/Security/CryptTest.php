<?php

/**
 * Part of Omega - Tests\Security Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Security;

use Omega\Security\Algo;
use Omega\Security\Crypt;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Crypt class.
 *
 * This test suite ensures the correct functionality of the Crypt component,
 * which provides encryption and decryption capabilities using various algorithms.
 *
 * It verifies that:
 * - Data encrypted using the default passphrase can be decrypted correctly.
 * - Encryption and decryption also work with custom passphrases.
 *
 * The tests rely on the AES-256-CBC algorithm and validate round-trip encryption integrity.
 *
 * @category  Omega\Tests
 * @package   Security
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Algo::class)]
#[CoversClass(Crypt::class)]
class CryptTest extends TestCase
{
    /** @var Crypt The Crypt instance used for testing encryption and decryption. */
    private Crypt $crypt;

    /**
     * Set up the test environment before each test.
     *
     * This method is called before each test method is run.
     * Override it to initialize objects, mock dependencies, or reset state.
     *
     * @return void
     */
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
        $plan_text = 'My secret message 1234';
        $encrypted = $this->crypt->encrypt($plan_text);
        $decrypted = $this->crypt->decrypt($encrypted);

        $this->assertEquals($plan_text, $decrypted);
    }

    /**
     * Test it can encrypt correctly with custom passphrase
     *
     * @return void
     */
    public function testItCanEncryptCorrectlyWithCustomPassphrase(): void
    {
        $plan_text = 'My secret message 1234';
        $encrypted = $this->crypt->encrypt($plan_text, 'secret');
        $decrypted = $this->crypt->decrypt($encrypted, 'secret');

        $this->assertEquals('My secret message 1234', $decrypted);
    }
}
