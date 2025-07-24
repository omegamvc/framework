<?php

/**
 * Part of Omega - Tests\Config Package
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Config\Config;
use Omega\Config\ConfigRepositoryInterface;
use Omega\Config\MergeStrategy;

use function class_implements;

/**
 * Unit tests for the Config class.
 *
 * This test suite validates the core behaviors of the Config implementation,
 * ensuring it behaves correctly with various operations such as storing, retrieving,
 * removing, and merging configuration values—both flat and nested.
 *
 * The test covers:
 * - Default state and interface compliance
 * - Basic get/set/remove operations
 * - Nested key access and default values
 * - Merging configuration repositories (including with nested keys)
 * - Handling of associative and indexed arrays
 *
 * @category  Tests
 * @package   Config
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Config::class)]
#[CoversClass(MergeStrategy::class)]
class ConfigRepositoryTest extends TestCase
{
    /** @var Config The current Config object. */
    private Config $configuration;

    /**
     * Set up the test environment before each test.
     *
     * This method is called before each test method is run.
     * Override it to initialize objects, mock dependencies, or reset state.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->configuration = new Config();
    }

    /**
     * Test it should implement configuration interface.
     * @return void
     */
    public function testItShouldImplementConfigurationInterface(): void
    {
        $this->assertContains(
            ConfigRepositoryInterface::class,
            class_implements($this->configuration::class)
        );
    }

    /**
     * Test it should be empty by default.
     *
     * @return void
     */
    public function testItShouldBeEmptyByDefault(): void
    {
        $this->assertEmpty($this->configuration->getAll());
    }

    /**
     * Test it should accept store.
     *
     * @return void
     */
    public function testItShouldAcceptStore(): void
    {
        $store         = ['key' => 'value'];
        $configuration = new Config($store);

        $this->assertEquals($store, $configuration->getAll());
    }

    /**
     * Test it should return all values.
     *
     * @return void
     */
    public function testItShouldReturnAllValues(): void
    {
        $this->configuration->set('key', 'value');

        $this->assertEquals(['key' => 'value'], $this->configuration->getAll());
    }

    /**
     * Test it should determine if key has value.
     *
     * @return void
     */
    public function testItShouldDetermineIfKeyHasValue(): void
    {
        $this->assertFalse($this->configuration->has('key'));

        $this->configuration->set('key', 'value');

        $this->assertTrue($this->configuration->has('key'));
    }

    /**
     * Test it should determine if key has nested value.
     *
     * @return void
     */
    public function testItShouldDetermineIfKeyHasNestedValue(): void
    {
        $this->assertFalse($this->configuration->has('nested.key'));

        $this->configuration->set('nested', ['key' => 'value']);

        $this->assertTrue($this->configuration->has('nested.key'));
    }

    /**
     * Test it should return null if key bot found.
     *
     * @return void
     */
    public function testItShouldReturnNullIfKeyNotFound(): void
    {
        $this->assertNull($this->configuration->get('key'));
    }

    /**
     * Test it should return null if nested key not found.
     *
     * @return void
     */
    public function testItShouldReturnNullIfNestedKeyNotFound(): void
    {
        $this->assertNull($this->configuration->get('nested.key'));
    }

    /**
     * Test it should return default value if provided.
     *
     * @return void
     */
    public function testItShouldReturnDefaultValueIfProvided(): void
    {
        $this->assertEquals('test', $this->configuration->get('key', 'test'));
    }

    /**
     * Test it should return value for key.
     *
     * @return void
     */
    public function testItShouldReturnValueForKey(): void
    {
        $configuration = new Config(['key' => 'value']);

        $this->assertEquals('value', $configuration->get('key'));
    }

    /**
     * Test it should return value for nested key.
     *
     * @return void
     */
    public function testItShouldReturnValueForNestedKey(): void
    {
        $configuration = new Config(['nested' => ['key' => 'value']]);

        $this->assertEquals('value', $configuration->get('nested.key'));
    }

    /**
     * @return void
     */
    public function testItShouldSetValueForKey(): void
    {
        $this->configuration->set('key', 'value');

        $this->assertEquals('value', $this->configuration->get('key'));
    }

    /**
     * Test it should set value for nested key.
     *
     * @return void
     */
    public function testItShouldSetValueForNestedKey(): void
    {
        $this->configuration->set('nested.key', 'value');

        $this->assertEquals(['key' => 'value'], $this->configuration->get('nested'));
    }

    /**
     * Test it should remove value from key.
     *
     * @return void
     */
    public function testItShouldRemoveValueForKey(): void
    {
        $configuration = new Config(['key' => 'value']);
        $configuration->remove('key');

        $this->assertFalse($configuration->has('key'));

        // Make sure no exception is thrown if the key doesn't exist.
        $this->configuration->remove('non_existing_key');
    }

    /**
     * Test it should remove value for nested key.
     *
     * @return void
     */
    public function testItShouldRemoveValueForNestedKey(): void
    {
        $configuration = new Config(['nested' => ['key' => 'value']]);
        $configuration->remove('nested.key');

        $this->assertFalse($configuration->has('nested.key'));
        $this->assertIsArray($configuration->get('nested'));
        $this->assertEmpty($configuration->get('nested'));

        // Make sure no exception is thrown if the key doesn't exist.
        $this->configuration->remove('nested.non_existing_key');
    }

    /**
     * Test it should clear all values.
     *
     * @return void
     */
    public function testItShouldClearAllValues(): void
    {
        $configuration = new Config(['key' => 'value']);
        $configuration->clear();

        $this->assertEmpty($configuration->getAll());
    }

    /**
     * Test it should merge configuration.
     *
     * @return void
     */
    public function testItShouldMergeConfiguration(): void
    {
        $configuration = new Config(['key' => 'value']);
        $another_configuration = new Config(['another_key' => 'another_value']);

        $configuration->merge($another_configuration);

        $this->assertEquals(
            [
                'key'         => 'value',
                'another_key' => 'another_value',
            ],
            $configuration->getAll()
        );
    }

    /**
     * Test it should merge configuration at key.
     *
     * @return void
     */
    public function testItShouldMergeConfigurationAtKey(): void
    {
        $configuration = new Config(['key' => 'value']);
        $anotherConfiguration = new Config(['another_key' => 'another_value']);

        $configuration->merge($anotherConfiguration, 'nested');

        $this->assertEquals(
            [
                'key'    => 'value',
                'nested' => [
                    'another_key' => 'another_value',
                ],
            ],
            $configuration->getAll()
        );
    }

    /**
     * Test it should merge configuration at nested key.
     *
     * @return void
     */
    public function testItShouldMergeConfigurationAtNestedKey(): void
    {
        $configuration = new Config(['key' => 'value']);
        $anotherConfiguration = new Config(['another_key' => 'another_value']);

        $configuration->merge($anotherConfiguration, 'nested.section');

        $this->assertEquals(
            [
                'key'    => 'value',
                'nested' => [
                    'section' => [
                        'another_key' => 'another_value',
                    ],
                ],
            ],
            $configuration->getAll()
        );
    }

    /**
     * Test it should merge configuration at existing key.
     *
     * @return void
     */
    public function testItShouldMergeConfigurationAtExistingKey(): void
    {
        $configuration = new Config(['nested' => ['key' => 'value']]);
        $anotherConfiguration = new Config(['another_key' => 'another_value']);

        $configuration->merge($anotherConfiguration, 'nested.section');

        $this->assertEquals(
            [
                'nested' => [
                    'key'     => 'value',
                    'section' => [
                        'another_key' => 'another_value',
                    ],
                ],
            ],
            $configuration->getAll()
        );
    }

    /**
     * Test it should merge associative arrays.
     *
     * @return void
     */
    public function testItShouldMergeAssociativeArrays(): void
    {
        $configuration = new Config(['nested' => ['key' => 'value']]);
        $anotherConfiguration = new Config(['nested' => ['another_key' => 'another_value']]);

        $configuration->merge($anotherConfiguration);

        $this->assertEquals(
            [
                'nested'  => [
                    'key' => 'value',
                    'another_key' => 'another_value',
                ],
            ],
            $configuration->getAll()
        );
    }

    /**
     * Test it should replace indexed arrays.
     *
     * @return void
     */
    public function testItShouldReplaceIndexedArrays(): void
    {
        $configuration = new Config(['indexed' => [1, 2, 3]]);
        $anotherConfiguration = new Config(['indexed' => [1, 2]]);

        $configuration->merge($anotherConfiguration);

        $this->assertEquals(['indexed' => [1, 2]], $configuration->getAll());
    }

    /**
     * Test it should merge indexed arrays.
     *
     * @return void
     */
    public function testItShouldMergeIndexedArrays(): void
    {
        $configuration = new Config(['indexed' => [1, 2, 3]]);
        $anotherConfiguration = new Config(['indexed' => [3, 4, 5]]);

        $configuration->merge($anotherConfiguration, null, MergeStrategy::MERGE_INDEXED);
        $this->assertEquals(['indexed' => [1, 2, 3, 4, 5]], $configuration->getAll());
    }
}
