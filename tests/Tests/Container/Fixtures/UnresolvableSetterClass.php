<?php

/**
 * Part of Omega - Tests\Container Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Container\Fixtures;

use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Class with a setter accepting an unresolvable dependency.
 * Used to test setter injection failure scenarios.
 *
 * @category   Tests
 * @package    Container
 * @subpackage Fixtures
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
#[CoversNothing]
class UnresolvableSetterClass
{
    /** @var mixed Holds the injected dependency. */
    public $dependency;

    /**
     * Setter with an unresolvable dependency type.
     *
     * @param UnresolvableInterface $dependency Dependency that cannot be resolved.
     */
    public function setUnresolvable(UnresolvableInterface $dependency): void
    {
        $this->dependency = $dependency;
    }
}
