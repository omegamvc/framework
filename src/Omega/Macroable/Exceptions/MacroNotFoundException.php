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

namespace Omega\Macroable\Exceptions;

use InvalidArgumentException;

use function sprintf;

/**
 * Exception thrown when attempting to call a macro
 * that has not been defined or registered.
 *
 * This exception is used internally by the MacroableTrait
 * to indicate that a requested macro method does not exist.
 *
 * @category  Omega
 * @package   Macroable
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @link      https://omegamvc.github.io
 */
class MacroNotFoundException extends InvalidArgumentException
{
    /**
     * Create a new MacroNotFoundException instance.
     *
     * @param string $methodName The name of the macro method that was called but not found.
     * @return void
     */
    public function __construct(string $methodName)
    {
        parent::__construct(
            sprintf(
                'The macro method `%s` was called, but is not defined. Please make sure it has been registered.',
                $methodName
            )
        );
    }
}
