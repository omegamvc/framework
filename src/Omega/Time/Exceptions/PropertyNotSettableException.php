<?php

/**
 * Part of Omega - Time Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Time\Exceptions;

/**
 * Exception thrown when attempting to set a property that is read-only or not settable.
 *
 * This is typically raised by the `Now` class when assigning values to properties
 * that cannot be modified via __set().
 *
 * @category   Omega
 * @package    Time
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class PropertyNotSettableException extends AbstractTimeException
{
    /**
     * Creates a new PropertyNotSettableException instance.
     *
     * @param string $propertyName The name of the property that was attempted to set.
     * @return void
     */
    public function __construct(string $propertyName)
    {
        parent::__construct('Property `%s` is not settable.', $propertyName);
    }
}
