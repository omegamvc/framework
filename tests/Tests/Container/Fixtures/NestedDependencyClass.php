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

use Omega\Container\Attribute\Inject;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Demonstrates nested dependency injection via setter methods.
 *
 * The container resolves and injects a dependant object,
 * which itself has dependencies.
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
class NestedDependencyClass
{
    /** @var Dependant|null Injected dependant instance */
    public ?Dependant $dependant = null;

    /**
     * Injects the dependant instance.
     *
     * @param Dependant $dependant Resolved dependant.
     * @return void
     */
    #[Inject]
    public function setDependant(Dependant $dependant): void
    {
        $this->dependant = $dependant;
    }
}
