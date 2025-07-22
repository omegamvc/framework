<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Console\Style;

use InvalidArgumentException;
use Omega\Console\Style\Color\BackgroundColor;
use Omega\Console\Style\Color\ForegroundColor;
use Omega\Text\Str;

use function min;
use function sscanf;

/**
 * Utility class for converting HEX and RGB color codes
 * into terminal color codes for text and background.
 *
 * Provides methods to create ForegroundColor and BackgroundColor
 * instances with true color support (24-bit).
 *
 * @category   Omega
 * @package    Console
 * @subpackage Style
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Colors
{
    /**
     * Convert a HEX color code to a ForegroundColor instance
     * suitable for terminal text coloring.
     *
     * @param string $hexCode A hex color string starting with '#', e.g. '#ff0000'
     * @return ForegroundColor Terminal color object representing the text color
     * @throws InvalidArgumentException if the hex code format is invalid
     */
    public static function hexText(string $hexCode): ForegroundColor
    {
        if (!Str::is($hexCode, '/^#[0-9a-fA-F]{6}$/i')) {
            throw new InvalidArgumentException('Hex code not found.');
        }

        [$r, $g, $b] = sscanf($hexCode, '#%02x%02x%02x');

        return self::rgbText($r, $g, $b);
    }

    /**
     * Convert a HEX color code to a BackgroundColor instance
     * suitable for terminal background coloring.
     *
     * @param string $hex_code A hex color string starting with '#', e.g. '#00ff00'
     * @return BackgroundColor Terminal color object representing the background color
     * @throws InvalidArgumentException if the hex code format is invalid
     */
    public static function hexBg(string $hex_code): BackgroundColor
    {
        if (!Str::is($hex_code, '/^#[0-9a-fA-F]{6}$/i')) {
            throw new InvalidArgumentException('Hex code not found.');
        }

        [$r, $g, $b] = sscanf($hex_code, '#%02x%02x%02x');

        return self::rgbBg($r, $g, $b);
    }

    /**
     * Convert RGB values to a ForegroundColor instance
     * for terminal text coloring.
     *
     * @param int $r Red channel (0-255)
     * @param int $g Green channel (0-255)
     * @param int $b Blue channel (0-255)
     * @return ForegroundColor Terminal color object representing the text color
     */
    public static function rgbText(int $r, int $g, int $b): ForegroundColor
    {
        return self::makeColor(38, $r, $g, $b, ForegroundColor::class);
    }

    /**
     * Convert RGB values to a BackgroundColor instance
     * for terminal background coloring.
     *
     * @param int $r Red channel (0-255)
     * @param int $g Green channel (0-255)
     * @param int $b Blue channel (0-255)
     * @return BackgroundColor Terminal color object representing the background color
     */
    public static function rgbBg(int $r, int $g, int $b): BackgroundColor
    {
        return self::makeColor(48, $r, $g, $b, BackgroundColor::class);
    }

    /**
     * Internal helper to create a terminal color object
     * by normalizing RGB values and instantiating
     * the given color class.
     *
     * @param int $code Terminal color code prefix (38 for foreground, 48 for background)
     * @param int $r Red channel normalized (0-255)
     * @param int $g Green channel normalized (0-255)
     * @param int $b Blue channel normalized (0-255)
     * @param class-string $class Fully qualified class name to instantiate (ForegroundColor or BackgroundColor)
     * @return object Instance of the specified color class
     */
    private static function makeColor(int $code, int $r, int $g, int $b, string $class): object
    {
        $r = max(0, min($r, 255));
        $g = max(0, min($g, 255));
        $b = max(0, min($b, 255));

        return new $class([$code, 2, $r, $g, $b]);
    }
}
