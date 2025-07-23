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

namespace Tests\Support\Bootstrap;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Application\Application;
use Omega\Collection\Collection;
use Omega\Support\Bootstrap\RegisterFacades;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function dirname;

/**
 * Class RegisterFacadesTest
 *
 * Unit test to verify the behavior of the RegisterFacades bootstrapper.
 *
 * This test ensures that the RegisterFacades class properly registers application
 * bindings with their corresponding facades, allowing static-like access to bound
 * services or classes.
 *
 * In this case, a Collection instance is registered in the container and validated
 * through the corresponding facade (`TestCollectionFacade`) to confirm proper linkage.
 *
 * @category   Omega\Tests
 * @package    Support
 * @subpackage Bootstrap
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Application::class)]
#[CoversClass(Collection::class)]
#[CoversClass(RegisterFacades::class)]
class RegisterFacadesTest extends TestCase
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanBootstrap(): void
    {
        $app = new Application(dirname(__DIR__, 2) . '/fixtures/support/bootstrap/app2/');
        $app->set(Collection::class, fn () => new Collection(['php' => 'great']));
        $app->bootstrapWith([RegisterFacades::class]);

        $this->assertTrue(TestCollectionFacade::has('php'));
    }
}
