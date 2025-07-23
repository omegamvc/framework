<?php

/**
 * Part of Omega - Tests\Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Support\Facades\Sample;

use Omega\Collection\Collection;
use Omega\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * FacadesTestClass
 *
 * A test facade class extending the base Facade to provide static access
 * to the Collection service within the Application container.
 *
 * This class is used solely for testing purposes in FacadeTest to verify
 * the facade behavior and static method forwarding.
 *
 * @category   Omega\Tests
 * @package    Facades
 * @subpackage Sample
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 *
 * @method static bool has(string $key) Checks if the collection contains the given key
 */
#[CoversNothing]
class FacadesTestClass extends Facade
{
    /**
     * Get the service accessor key for the facade.
     *
     * This method tells the facade which service in the container it proxies.
     *
     * @return string The container service identifier (class name)
     */
    protected static function getAccessor(): string
    {
        return Collection::class;
    }
}
