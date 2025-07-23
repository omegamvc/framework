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

namespace Tests\Cache\Storage;

use Omega\Cache\Storage\FileStorage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function dirname;
use function is_dir;
use function rmdir;
use function scandir;
use function unlink;

/**
 * Unit test suite for the FileStorage cache implementation.
 *
 * This class validates the behavior of the FileStorage component, which persists cache
 * items to the file system. It covers core operations such as storing, retrieving,
 * deleting, and clearing cached data. It also tests more advanced features like TTL
 * expiration, atomic increment/decrement, and fallback defaults.
 *
 * Each test ensures that FileStorage complies with expected cache interface behavior,
 * providing reliable, file-based caching capabilities for the Omega framework.
 *
 * @category   Omega\Tests
 * @package    Cache
 * @subpackage Storage
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(FileStorage::class)]
class FileStorageTest extends TestCase
{
    /** @var FileStorage Holds the current file storage instance. */
    protected FileStorage $storage;

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
        $this->storage = new FileStorage(dirname(__DIR__, 2) . '/fixtures/cache');
    }

    /**
     * Clean up the test environment after each test.
     *
     * This method flushes and resets the application container
     * to ensure a clean state between tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $cacheDir = dirname(__DIR__, 2) . '/fixtures/cache';

        if (!is_dir($cacheDir)) {
            return;
        }

        $deleteRecursive = function (string $dir) use (&$deleteRecursive) {
            $items = array_diff(scandir($dir), ['.', '..']);
            foreach ($items as $item) {
                $path = $dir . DIRECTORY_SEPARATOR . $item;
                if (is_dir($path)) {
                    $deleteRecursive($path);
                } else {
                    @unlink($path);
                }
            }
            @rmdir($dir);
        };

        $items = array_diff(scandir($cacheDir), ['.', '..']);
        foreach ($items as $item) {
            $path = $cacheDir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $deleteRecursive($path);
            } else {
                @unlink($path);
            }
        }
    }

    /**
     * Test set and get.
     *
     * @return void
     */
    public function testSetAndGet(): void
    {
        $this->assertTrue($this->storage->set('key1', 'value1'));
        $this->assertEquals('value1', $this->storage->get('key1'));
    }

    /**
     * Test get with default.
     *
     * @return void
     */
    public function testGetWithDefault(): void
    {
        $this->assertEquals('default', $this->storage->get('non_existing_key', 'default'));
    }

    /**
     * Test set expires after ttl.
     *
     * @return void
     */
    public function testSetExpiresAfterTtl(): void
    {
        //$this->markTestSkipped('sleep is not allowed');
        $this->assertTrue($this->storage->set('key2', 'value2', 1));
        sleep(2);
        $this->assertNull($this->storage->get('key2'));
    }

    /**
     * Test delete.
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->storage->set('key3', 'value3');
        $this->assertTrue($this->storage->delete('key3'));
        $this->assertFalse($this->storage->has('key3'));
    }

    /**
     * Test delete returns false for missing key.
     *
     * @return void
     */
    public function testDeleteReturnsFalseForMissingKey(): void
    {
        $this->assertFalse($this->storage->delete('non_existing_key'));
    }

    /**
     * Test clear.
     *
     * @return void
     */
    public function testClear(): void
    {
        $this->storage->set('key4', 'value4');
        $this->assertTrue($this->storage->clear());
        $this->assertFalse($this->storage->has('key4'));
    }

    /**
     * Test get multiple with fallback for missing keys.
     *
     * @return void
     */
    public function testGetMultipleWithFallbackForMissingKeys(): void
    {
        $this->storage->set('key5', 'value5');
        $this->storage->set('key6', 'value6');
        $result = $this->storage->getMultiple(['key5', 'key6', 'non_existing_key'], 'default');
        $this->assertEquals(['key5' => 'value5', 'key6' => 'value6', 'non_existing_key' => 'default'], $result);
    }

    /**
     * Test set multiple.
     *
     * @return void
     */
    public function testSetMultiple(): void
    {
        $result = $this->storage->setMultiple(['key7' => 'value7', 'key8' => 'value8']);

        $this->assertTrue($result);

        $this->assertEquals('value7', $this->storage->get('key7'));
        $this->assertEquals('value8', $this->storage->get('key8'));
    }

    /**
     * Test delete multiple.
     *
     * @return void
     */
    public function testDeleteMultiple(): void
    {
        $this->storage->set('key9', 'value9');
        $this->storage->set('key10', 'value10');
        $this->assertTrue($this->storage->deleteMultiple(['key9', 'key10']));
        $this->assertFalse($this->storage->has('key9'));
        $this->assertFalse($this->storage->has('key10'));
    }

    /**
     * Test has.
     *
     * @return void
     */
    public function testHas(): void
    {
        $this->storage->set('key11', 'value11');
        $this->assertTrue($this->storage->has('key11'));
        $this->assertFalse($this->storage->has('non_existing_key'));
    }

    /**
     * Test increment increases value correctly.
     *
     * @return void
     */
    public function testIncrementIncreasesValueCorrectly(): void
    {
        $this->assertEquals(10, $this->storage->increment('key12', 10));
        $this->assertEquals(20, $this->storage->increment('key12', 10));
    }

    /**
     * Test decrement reduces value correctly.
     *
     * @return void
     */
    public function testDecrementReducesValueCorrectly(): void
    {
        $this->storage->set('key13', 20);
        $this->assertEquals(10, $this->storage->decrement('key13', 10));
    }

    /**
     * Test get info returns metadata.
     *
     * @return void
     */
    public function testGetInfoReturnsMetadata(): void
    {
        $this->storage->set('key14', 'value14');
        $info = $this->storage->getInfo('key14');
        $this->assertArrayHasKey('value', $info);
        $this->assertEquals('value14', $info['value']);
    }

    /**
     * Test remember caches result and returnsIt.
     *
     * @return void
     */
    public function testRememberCachesResultAndReturnsIt(): void
    {
        $value = $this->storage->remember('key1', fn(): string => 'value1', 1);
        $this->assertEquals('value1', $value);
    }
}
