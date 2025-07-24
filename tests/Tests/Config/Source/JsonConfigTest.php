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

namespace Tests\Config\Source;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Config\Exceptions\MalformedJsonException;
use Omega\Config\Source\JsonConfig;

use function dirname;

/**
 * Unit tests for the JsonConfig configuration source.
 *
 * Verifies that valid JSON files are parsed correctly into arrays,
 * and that malformed files trigger appropriate exceptions.
 *
 * @category   Tests
 * @package    Config
 * @subpackage Source
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(MalformedJsonException::class)]
#[CoversClass(JsonConfig::class)]
class JsonConfigTest extends TestCase
{
    /**
     * Test it should return values.
     *
     * @return void
     */
    public function testItShouldReturnValues(): void
    {
        $configurationSource = new JsonConfig(dirname(__DIR__, 2) . '/fixtures1/config/content.json');

        $this->assertEquals(['key' => 'value'], $configurationSource->fetch());
    }

    /**
     * Test it should throw on malformed configuration.
     *
     * @return void
     */
    public function testItShouldThrowOnMalformedConfiguration(): void
    {
        $this->expectException(MalformedJsonException::class);

        $configurationSource = new JsonConfig(dirname(__DIR__, 2) . '/fixtures1/config/malformed.json');
        $configurationSource->fetch();
    }
}
