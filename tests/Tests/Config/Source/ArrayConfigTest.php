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
use Omega\Config\Source\ArrayConfig;

/**
 * Unit tests for the ArrayConfig configuration source.
 *
 * Ensures that array-based configuration is correctly returned,
 * and validates handling of basic input scenarios.
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
#[CoversClass(ArrayConfig::class)]
class ArrayConfigTest extends TestCase
{
    /**
     * Test it should return content.
     *
     * @return void
     */
    public function testItShouldReturnContent(): void
    {
        $content = ['key' => 'value'];
        $configurationSource = new ArrayConfig($content);

        $this->assertEquals($content, $configurationSource->fetch());
    }
}
