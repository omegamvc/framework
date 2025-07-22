<?php

/**
 * Part of Omega - Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Container\Exceptions;

use InvalidArgumentException;

/**
 * Exception thrown when an alias conflicts with its original abstract.
 *
 * This occurs when an alias is set to reference itself, which would cause
 * infinite recursion in alias resolution. Use this to handle incorrect
 * or circular alias definitions safely.
 *
 * @category   Omega
 * @package    Container
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class AliasConflictException extends InvalidArgumentException implements ContainerExceptionInterface
{
}
