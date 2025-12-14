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
 * Fixture class with a non-nullable union-typed constructor dependency.
 *
 * Used to verify that the container resolves one of the allowed union types.
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
class ClassWithUnionTypeConstructor
{
    /** @var UnionDependencyOne|UnionDependencyTwo Holds the resolved union-typed dependency. */
    public UnionDependencyOne|UnionDependencyTwo $dependency;

    /**
     * @param UnionDependencyOne|UnionDependencyTwo $dependency Required union-typed dependency.
     */
    public function __construct(UnionDependencyOne|UnionDependencyTwo $dependency)
    {
        $this->dependency = $dependency;
    }
}
