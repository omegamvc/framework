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

use Omega\Console\Traits\AlertTrait;

/**
 * Class Alert
 *
 * Represents a user interface alert component.
 * Provides methods to create and render alert messages with predefined styles and behavior.
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
final class Alert
{
    use AlertTrait;

    /**
     * Creates a new instance of Alert.
     *
     * This static method acts as a factory for easy instantiation.
     *
     * @return Alert A new Alert instance ready for configuration and rendering.
     */
    public static function render(): Alert
    {
        return new self();
    }
}
