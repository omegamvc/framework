<?php

/**
 * Part of Omega - Support Package.
 * php  version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facade\Exception;

use RuntimeException;

/**
 * Exception thrown when the underlying instance of a facade has not been set.
 *
 * The `FacadeObjectNotSetException` is triggered by the facade pattern when
 * the instance that the facade should reference is not found in the application
 * container. It usually indicates that the facade has not been properly registered
 * or the container has not been correctly configured.
 *
 * @category   Omega
 * @package    Facade
 * @subpackage Exception
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class FacadeObjectNotSetException extends RuntimeException
{
    public function __construct(string $className)
    {
        parent::__construct(
            "The facade instance for '{$className}' has not been set.
            Please ensure that the facade is registered with the application container and '
            . 'that the container is configured correctly."
        );
    }
}
