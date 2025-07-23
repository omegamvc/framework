<?php

/**
 * Part of Omega - Tests\Cache Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Cache;

use Omega\Cache\Cache;
use Omega\Cache\CacheInterface;
use Omega\Cache\Storage\ArrayStorage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit test suite for the Cache facade.
 *
 * This class verifies the behavior of the Cache abstraction layer, ensuring
 * correct driver registration, selection, and interaction. It tests the ability
 * to set and retrieve the default cache driver, as well as custom-named drivers.
 *
 * The test cases confirm that cache operations such as set and get work as expected
 * when using different underlying storage implementations.
 *
 * @category  Omega\Tests
 * @package   Cache
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Cache::class)]
#[CoversClass(ArrayStorage::class)]
class CacheTest extends TestCase
{
    /**
     * Test set default driver.
     *
     * @return void
     */
    public function testSetDefaultDriver(): void
    {
        $cache = new Cache();
        $cache->setDefaultDriver(new ArrayStorage());
        $this->assertInstanceOf(CacheInterface::class, $cache->driver());

        $this->assertTrue($cache->set('key1', 'value1'));
        $this->assertEquals('value1', $cache->get('key1'));
    }

    /**
     * Test can register and use named driver.
     *
     * @return void
     */
    public function testCanRegisterAndUseNamedDriver(): void
    {
        $cache = new Cache();
        $cache->setDriver('array2', fn (): CacheInterface => new ArrayStorage());
        $this->assertInstanceOf(CacheInterface::class, $cache->driver('array2'));

        $this->assertTrue($cache->driver('array2')->set('key1', 'value1'));
        $this->assertEquals('value1', $cache->driver('array2')->get('key1'));
    }
}
