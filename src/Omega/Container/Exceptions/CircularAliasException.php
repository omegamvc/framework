<?php

/**
 * Part of Omega - Container Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Container\Exceptions;

use function sprintf;

/**
 * Exception thrown when a circular alias reference is detected in the container.
 *
 * @category   Omega
 * @package    Container
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class CircularAliasException extends BindingResolutionException
{
    /**
     * Constructor.
     *
     * @param string $abstract The alias that is part of the circular reference.
     */
    public function __construct(string $abstract)
    {
        parent::__construct(sprintf("Circular alias reference detected for %s", $abstract));
    }
}
