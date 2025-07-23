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

use DateInterval;
use DateTime;
use Omega\Cache\Storage\ArrayStorage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function time;

/**
 * Unit test suite for the ArrayStorage class.
 *
 * This test class covers the functionality of the in-memory array-based cache
 * implementation. It verifies behaviors such as setting, getting, deleting,
 * expiration, increment/decrement, and utility methods like remember().
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
#[CoversClass(ArrayStorage::class)]
class ArrayStorageTest extends TestCase
{
    /** @var ArrayStorage Holds the current ArrayStorage instance. */
    protected ArrayStorage $storage;

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
        $this->storage = new ArrayStorage(3_600);
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
     * Test set with TTL.
     *
     * @return void
     */
    public function testSetWithTtl(): void
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
     * Test delete non existing key.
     *
     * @return void
     */
    public function testDeleteNonExistingKey(): void
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
     * Test get multiple.
     *
     * @return void
     */
    public function testGetMultiple(): void
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
        $this->assertFalse($this->storage->setMultiple(['key7' => 'value7', 'key8' => 'value8']));
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
     * Test increment.
     *
     * @return void
     */
    public function testIncrement(): void
    {
        $this->assertEquals(10, $this->storage->increment('key12', 10));
        $this->assertEquals(20, $this->storage->increment('key12', 10));
    }

    /**
     * Test decrement.
     *
     * @return void
     */
    public function testDecrement(): void
    {
        $this->storage->increment('key13', 20);
        $this->assertEquals(10, $this->storage->decrement('key13', 10));
    }

    /**
     * Test getInfo.
     *
     * @return void
     */
    public function testGetInfo(): void
    {
        $this->storage->set('key14', 'value14');
        $info = $this->storage->getInfo('key14');
        $this->assertArrayHasKey('value', $info);
        $this->assertEquals('value14', $info['value']);
    }

    /**
     * Test calculate expiration timestamp.
     *
     * @return void
     */
    public function testCalculateExpirationTimestamp(): void
    {
        $time = time();
        // null
        $expired = (fn () => $this->{'calculateExpirationTimestamp'}(null))->call($this->storage);
        $this->assertGreaterThanOrEqual($time, $expired);
        // int
        $expired = (fn () => $this->{'calculateExpirationTimestamp'}(time()))->call($this->storage);
        $this->assertGreaterThanOrEqual($time, $expired);
        // date interval
        $expired = (fn () => $this->{'calculateExpirationTimestamp'}(DateInterval::createFromDateString('1 day')))->call($this->storage);
        $this->assertGreaterThanOrEqual($time, $expired);
        // date time
        $expired = (fn () => $this->{'calculateExpirationTimestamp'}(new DateTime()))->call($this->storage);
        $this->assertGreaterThanOrEqual($time, $expired);
    }

    /**
     * Test is expired.
     *
     * @return void
     */
    public function testIsExpired(): void
    {
        $expired = (fn () => $this->{'isExpired'}(time() + 2))->call($this->storage);
        $this->assertFalse($expired);
    }

    /**
     * Test createMime.
     *
     * @return void
     */
    public function testCreateTime(): void
    {
        $mtime = (fn () => $this->{'createMtime'}())->call($this->storage);
        $this->assertIsFloat($mtime);
    }

    /**
     * Test remember.
     *
     * @return void
     */
    public function testRemember(): void
    {
        $value = $this->storage->remember('key1', fn(): string => 'value1', 1);
        $this->assertEquals('value1', $value);
    }
}
