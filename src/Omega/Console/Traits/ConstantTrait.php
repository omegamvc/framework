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

namespace Omega\Console\Traits;

use function constant;

/**
 * Trait ConstantTrait
 *
 * Provides a utility method to dynamically resolve class constants by name.
 * Useful for retrieving styling constants like color codes or predefined labels
 * from string inputs at runtime.
 *
 * Example usage:
 * ```php
 * $value = self::getConstant('COLOR_RED');
 * ```
 * @category   Omega
 * @package    Console
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
trait ConstantTrait
{
    /**
     * Retrieve a class constant value by name.
     *
     * Accepts the name of a constant as a string and returns its value.
     * Particularly useful when mapping names to color codes or other fixed values.
     *
     * @param string $name The constant name (e.g., 'COLOR_RED').
     * @return string|int The value of the constant (e.g., a color name or hex code).
     *
     * @throws \Error If the constant does not exist.
     */
    public static function getConstant(string $name): string|int
    {
        return constant("self::$name");
    }
}
