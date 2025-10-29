<?php

/**
 * Part of Omega - Tests\Cache Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Cache;

use Omega\Cache\CacheFactory;
use Omega\Cache\CacheInterface;
use Omega\Cache\Exceptions\UnknownStorageException;
use Omega\Cache\Storage\File;
use Omega\Cache\Storage\Memory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class CacheFactoryTest
 *
 * This test suite verifies the behavior of the CacheFactory class, ensuring that
 * it can correctly register and resolve cache drivers. Each test focuses on the
 * factory's ability to instantiate a specific type of cache storage (e.g., File, Memory),
 * without testing the internal logic of the storage implementations themselves.
 *
 * The goal of these tests is to guarantee that:
 * 1. Drivers can be registered via setDriver() with either an instance or a closure.
 * 2. The factory correctly resolves drivers by name.
 * 3. The factory returns an object implementing CacheInterface for any registered driver.
 *
 * Note:
 * - This suite does not test the functional behavior of individual storage drivers.
 * - Storage drivers with external dependencies (e.g., database, Redis) should be tested
 *   separately with dedicated tests and/or mocks.
 *
 * @category  Tests
 * @package   Cache
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(CacheFactory::class)]
#[CoversClass(File::class)]
#[CoversClass(Memory::class)]
class CacheFactoryTest extends TestCase
{
    /**
     * Test file factory.
     *
     * @return void
     * @throws UnknownStorageException
     */
    public function testFileFactory(): void
    {
        $cache = new CacheFactory();
        $cache->setDriver('array1', fn (): CacheInterface => new File(['ttl' => 3600, 'path' => '/cache']));
        $this->assertInstanceOf(CacheInterface::class, $cache->getDriver('array1'));

        $this->assertTrue($cache->getDriver('array1')->set('key1', 'value1'));
        $this->assertEquals('value1', $cache->getDriver('array1')->get('key1'));
    }

    /**
     * Test memory factory.
     *
     * @return void
     * @throws UnknownStorageException
     */
    public function testMemoryFactory(): void
    {
        $cache = new CacheFactory();
        $cache->setDriver('array2', fn (): CacheInterface => new Memory(['ttl' => 3600]));
        $this->assertInstanceOf(CacheInterface::class, $cache->getDriver('array2'));

        $this->assertTrue($cache->getDriver('array2')->set('key1', 'value1'));
        $this->assertEquals('value1', $cache->getDriver('array2')->get('key1'));
    }
}
