<?php

/**
 * Part of Omega - Macroable Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Macroable;

/**
 * Class Macroable
 *
 * Concrete implementation of the MacroableTrait, used to test
 * and demonstrate macro functionality in isolation.
 *
 * This class is primarily intended for internal testing purposes
 * and is not part of the public API. It provides a simple context
 * where the MacroableTrait can be used and evaluated independently.
 *
 * @category  Omega
 * @package   Macroable
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @internal This class is meant for internal use only and may change without notice.
 */
class Macroable implements MacroableInterface
{
    use MacroableTrait;
}
