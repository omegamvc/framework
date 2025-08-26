<?php

declare(strict_types=1);

namespace Omega\Console\Style;


use Omega\Console\Exceptions\InvalidHexCodeException;
use Omega\Console\Style\Color\BackgroundColor;
use Omega\Console\Style\Color\ForegroundColor;
use Omega\Text\Str;

use function sscanf;

class Colors
{
    /**
     * Convert hex color to terminal color raw (text).
     *
     * @param string $hexCode Hex code (start with #)
     * @return ForegroundColor Terminal color
     * @throws InvalidHexCodeException if hex code is not found.
     */
    public static function hexText(string $hexCode): ForegroundColor
    {
        if (!Str::is($hexCode, '/^#[0-9a-fA-F]{6}$/i')) {
            throw new InvalidHexCodeException('Hex code not found.');
        }

        [$r, $g, $b] = sscanf($hexCode, '#%02x%02x%02x');

        return self::rgbText($r, $g, $b);
    }

    /**
     * Convert hex color to terminal color raw (background).
     *
     * @param string $hexCode Hex code (start with #)
     * @return BackgroundColor Terminal color
     * @throws InvalidHexCodeException if hex code is not found.
     */
    public static function hexBg(string $hexCode): BackgroundColor
    {
        if (!Str::is($hexCode, '/^#[0-9a-fA-F]{6}$/i')) {
            throw new InvalidHexCodeException('Hex code not found.');
        }

        [$r, $g, $b] = sscanf($hexCode, '#%02x%02x%02x');

        return self::rgbBg($r, $g, $b);
    }

    /**
     * Convert rgb color (true color) to terminal color raw (text).
     *
     * @param int $r Red (0-255)
     * @param int $g Green (0-255)
     * @param int $b Blue (0-255)
     * @return ForegroundColor Terminal code
     */
    public static function rgbText(int $r, int $g, int $b): ForegroundColor
    {
        return new ForegroundColor(self::buildRgbArray(38, $r, $g, $b));
    }

    /**
     * Convert rgb color to terminal color raw (background).
     *
     * @param int $r Red (0-255)
     * @param int $g Green (0-255)
     * @param int $b Blue (0-255)
     * @return BackgroundColor Terminal code
     */
    public static function rgbBg(int $r, int $g, int $b): BackgroundColor
    {
        return new BackgroundColor(self::buildRgbArray(48, $r, $g, $b));
    }

    /**
     * Build a normalized RGB array for terminal color codes.
     *
     * This method normalizes RGB values to the range 0–255 and
     * returns an array formatted for terminal color construction.
     *
     * @param int $prefix Terminal color prefix code (38 for foreground, 48 for background)
     * @param int $r Red channel (0–255)
     * @param int $g Green channel (0–255)
     * @param int $b Blue channel (0–255)
     * @return int[] Returns an array with the following structure:
     *   [
     *     0 => int $prefix,  // terminal color type code (38 or 48)
     *     1 => int 2,        // mode for true-color
     *     2 => int $r,       // normalized red channel
     *     3 => int $g,       // normalized green channel
     *     4 => int $b        // normalized blue channel
     *   ]
     * @throws InvalidHexCodeException if the terminal color prefix is not 38 (foreground) or 48 (background).
 */
    private static function buildRgbArray(int $prefix, int $r, int $g, int $b): array
    {
        if ($prefix !== 38 && $prefix !== 48) {
            throw new InvalidHexCodeException('Invalid terminal color prefix. Must be 38 or 48.');
        }

        $r = max(0, min($r, 255));
        $g = max(0, min($g, 255));
        $b = max(0, min($b, 255));

        return [$prefix, 2, $r, $g, $b];
    }
}
