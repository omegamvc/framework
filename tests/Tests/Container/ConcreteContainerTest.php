<?php

/**
 * Part of Omega - Tests\Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Container;

use Omega\Container\Container;
use Omega\Container\ContainerInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Concrete implementation of the abstract container interface test suite.
 *
 * This class provides a specific Container instance to run the shared tests
 * defined in AbstractContainerInterfaceTest. It ensures that the Container
 * implementation complies with the expected ContainerInterface behavior.
 *
 * @category  Omega\Tests
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Container::class)]
class ConcreteContainerTest extends AbstractContainerInterface
{
    /**
     * Returns a new instance of the Container implementation.
     *
     * Used by the abstract parent test class to perform interface-level tests
     * against a concrete, functional container instance.
     *
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return new Container();
    }
}
