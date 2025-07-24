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
use Omega\Config\ConfigBuilder;
use Omega\Config\MergeStrategy;
use Omega\Config\Source\ArrayConfig;

/**
 * Unit tests for the ConfigBuilder class.
 *
 * This test suite verifies the behavior of the ConfigBuilder component,
 * which is responsible for aggregating and merging multiple configuration
 * sources into a single configuration object.
 *
 * Covered scenarios include:
 * - Handling of empty configuration sources.
 * - Merging of multiple configuration arrays.
 * - Recursive merging of nested configurations.
 * - Replacing vs. merging of indexed arrays.
 * - Injecting configuration at specific keys or nested keys.
 *
 * The tests ensure that different merging strategies and edge cases are
 * correctly handled to produce the expected configuration result.
 *
 * @category  Tests
 * @package   Config
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(ConfigBuilder::class)]
#[CoversClass(MergeStrategy::class)]
#[CoversClass(ArrayConfig::class)]
class ConfigBuilderTest extends TestCase
{
    /** @var ConfigBuilder The current ConfigBuilder object. */
    private ConfigBuilder $configurationBuilder;

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
        $this->configurationBuilder = new ConfigBuilder();
    }

    /**
     * Test it should produce empty configuration object if no sources.
     *
     * @return void
     */
    public function testItShouldProduceEmptyConfigurationObjectIfNoSources(): void
    {
        $this->assertEmpty($this->configurationBuilder->build()->getAll());
    }

    /**
     * Test it should accept configuration source.
     *
     * @return void
     */
    public function testItShouldAcceptConfigurationSource(): void
    {
        $content = ['key' => 'value'];

        $this->assertEquals(
            $content,
            $this->configurationBuilder
                ->addConfiguration(new ArrayConfig($content))
                ->build()
                ->getAll()
        );
    }

    /**
     * test it should merge configuration source contents.
     *
     * @return void
     */
    public function testItShouldMergeConfigurationSourceContents(): void
    {
        $source_1 = new ArrayConfig(['key' => 'value']);
        $source_2 = new ArrayConfig(['another_key' => 'another_value']);

        $this->assertEquals(
            [
                'key'         => 'value',
                'another_key' => 'another_value',
            ],
            $this->configurationBuilder
                ->addConfiguration($source_1)
                ->addConfiguration($source_2)
                ->build()
                ->getAll()
        );
    }

    /**
     * Test it should merge configuration source contents recursive.
     *
     * @return void
     */
    public function testItShouldMergeConfigurationSourceContentsRecursively(): void
    {
        $source_1 = new ArrayConfig(['nested' => ['key' => 'value']]);
        $source_2 = new ArrayConfig(['nested' => ['another_key' => 'another_value']]);

        $this->assertEquals(
            [
                'nested'          => [
                    'key'         => 'value',
                    'another_key' => 'another_value',
                ],
            ],
            $this->configurationBuilder
                ->addConfiguration($source_1)
                ->addConfiguration($source_2)
                ->build()
                ->getAll()
        );
    }

    /**
     * Test it should replace indexed arrays in configuration source contents.
     *
     * @return void
     */
    public function testItShouldReplaceIndexedArraysInConfigurationSourceContents(): void
    {
        $source_1 = new ArrayConfig(['key' => [1, 2, 3]]);
        $source_2 = new ArrayConfig(['key' => [1, 2]]);

        $this->assertEquals(
            [
                'key' => [1, 2],
            ],
            $this->configurationBuilder
                ->addConfiguration($source_1)
                ->addConfiguration($source_2)
                ->build()
                ->getAll()
        );
    }

    /**
     * Test it should merge indexed array in configuration source contents.
     *
     * @return void
     */
    public function testItShouldMergeIndexedArraysInConfigurationSourceContents(): void
    {
        $source_1 = new ArrayConfig(['key' => [1, 2, 3]]);
        $source_2 = new ArrayConfig(['key' => [3, 4, 5]]);

        $this->assertEquals(
            [
                'key' => [1, 2, 3, 4, 5],
            ],
            $this->configurationBuilder
                ->addConfiguration($source_1)
                ->addConfiguration($source_2)
                ->build(MergeStrategy::from(MergeStrategy::MERGE_INDEXED))
                ->getAll()
        );
    }

    /**
     * Test it should merge configuration source contents at key.
     *
     * @return void
     */
    public function testItShouldMergeConfigurationSourceContentsAtKey(): void
    {
        $source_1 = new ArrayConfig(['key' => 'value']);
        $source_2 = new ArrayConfig(['another_key' => 'another_value']);

        $this->assertEquals(
            [
                'key'    => 'value',
                'nested' => [
                    'another_key' => 'another_value',
                ],
            ],
            $this->configurationBuilder
                ->addConfiguration($source_1)
                ->addConfiguration($source_2, 'nested')
                ->build()
                ->getAll()
        );
    }

    /**
     * Test it should merge configuration source content ar nested key.
     *
     * @return void
     */
    public function testItShouldMergeConfigurationSourceContentsAtNestedKey(): void
    {
        $source_1 = new ArrayConfig(['key' => 'value']);
        $source_2 = new ArrayConfig(['another_key' => 'another_value']);

        $this->assertEquals(
            [
                'key'    => 'value',
                'nested' => [
                    'section' => [
                        'another_key' => 'another_value',
                    ],
                ],
            ],
            $this->configurationBuilder
                ->addConfiguration($source_1)
                ->addConfiguration($source_2, 'nested.section')
                ->build()
                ->getAll()
        );
    }

    /**
     * Test it should merge configuration source contents at existing key.
     *
     * @return void
     */
    public function testItShouldMergeConfigurationSourceContentsAtExistingKey(): void
    {
        $source_1 = new ArrayConfig(['nested' => ['key' => 'value']]);
        $source_2 = new ArrayConfig(['another_key' => 'another_value']);

        $this->assertEquals(
            [
                'nested'  => [
                    'key' => 'value',
                    'another_key' => 'another_value',
                ],
            ],
            $this->configurationBuilder
                ->addConfiguration($source_1)
                ->addConfiguration($source_2, 'nested')
                ->build()
                ->getAll()
        );
    }
}
