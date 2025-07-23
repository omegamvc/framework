<?php

/**
 * Part of Omega - Tests\Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Console\Style;

use Omega\Console\Style\Colors;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Unit tests for the Colors class.
 *
 * This test suite verifies the conversion of color values into terminal-compatible
 * ANSI escape codes, including:
 * - Hexadecimal foreground (`hexText`) and background (`hexBg`) color codes.
 * - RGB foreground (`rgbText`) and background (`rgbBg`) color codes.
 * - Exception handling for invalid or malformed color input.
 *
 * These tests ensure the correctness and robustness of terminal color formatting
 * logic used throughout the application.
 *
 * @category   Omega\Tests
 * @package    Console
 * @subpackage Style
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Colors::class)]
class ColorsTest extends TestCase
{
    /**
     * Test it can convert hex text to terminal color code.
     *
     * @return void
     */
    public function testItCanConvertHexTextToTerminalColorCode(): void
    {
        $this->assertEquals('38;2;255;255;255', Colors::hexText('#ffffff')->raw());
        $this->assertEquals('38;2;255;255;255', Colors::hexText('#FFFFFF')->raw());

        try {
            $this->assertEquals('38;5;231', Colors::hexText('ffffff'));
        } catch (Throwable $th) {
            $this->assertEquals('Hex code not found.', $th->getMessage());
        }

        try {
            $this->assertEquals('38;5;231', Colors::hexText('#badas'));
        } catch (Throwable $th) {
            $this->assertEquals('Hex code not found.', $th->getMessage());
        }
    }

    /**
     * Test it can convert hex bg to terminal color code.
     *
     * @return void
     */
    public function testItCanConvertHexBgToTerminalColorCode(): void
    {
        $this->assertEquals('48;2;255;255;255', Colors::hexBg('#ffffff')->raw());
        $this->assertEquals('48;2;255;255;255', Colors::hexBg('#FFFFFF')->raw());

        try {
            $this->assertEquals('48;5;231', Colors::hexBg('ffffff')->raw());
        } catch (Throwable $th) {
            $this->assertEquals('Hex code not found.', $th->getMessage());
        }

        try {
            $this->assertEquals('48;5;231', Colors::hexBg('#badas')->raw());
        } catch (Throwable $th) {
            $this->assertEquals('Hex code not found.', $th->getMessage());
        }
    }

    /**
     * Test it can convert rgb to terminal color code.
     *
     * @return void
     */
    public function testItCanConvertRGBToTerminalColorCode(): void
    {
        $this->assertEquals([38, 2, 0, 0, 0], Colors::rgbText(0, 0, 0)->get(), 'rgb text color white');
        $this->assertEquals([48, 2, 0, 0, 0], Colors::rgbBg(0, 0, 0)->get(), 'rgb bg color white');
    }
}
