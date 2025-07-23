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

namespace Tests\Security\Hashing;

use Omega\Security\Hashing\BcryptHasher;
use Omega\Security\Hashing\HashManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the HashManager class.
 *
 * This test suite verifies the correct behavior of the hashing manager,
 * which handles password hashing, verification, and algorithm validation
 * using different hash drivers.
 *
 * It ensures that:
 * - The default hasher produces secure and verifiable hashes.
 * - Custom hash drivers (like Bcrypt) can be registered and used properly.
 * - Hashes are not reversible and comply with the expected algorithm format.
 *
 * @category   Omega\Tests
 * @package    Security
 * @subpackage Hashing
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(BcryptHasher::class)]
#[COversClass(HashManager::class)]
class HasherMangerTest extends TestCase
{
    /**
     * Test it can hash efault hasher.
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
    public function testItCanUseDriver(): void
    {
        $hasher = new HashManager();
        $hasher->setDriver('bcrypt', new BcryptHasher());
        $hash   = $hasher->driver('bcrypt')->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->driver('bcrypt')->verify('password', $hash));
        $this->assertTrue($hasher->driver('bcrypt')->isValidAlgorithm($hash));
    }
}
