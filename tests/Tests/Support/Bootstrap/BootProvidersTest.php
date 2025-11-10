<?php

/**
 * Part of Omega - Tests\Support\Bootstrap Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Support\Bootstrap;

use Exception;
use Omega\Application\Application;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Support\Bootstrap\BootProviders;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class BootProvidersTest
 *
 * This test ensures that the application can successfully bootstrap its
 * service providers using the BootProviders bootstrapper. The test confirms
 * that before the bootstrap process the application is not yet initialized,
 * and that after invoking `bootstrapWith()` the application transitions to
 * a booted state.
 *
 * This verifies the correct integration between the application's service
 * provider registration system and the bootstrap loading mechanism.
 *
 * @category   Tests
 * @package    Support
 * @subpackage Bootstrap
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Application::class)]
#[CoversClass(BootProviders::class)]
class BootProvidersTest extends TestCase
{
    /**
     * Test bootstrap
     *
     * @return void
     * @throws InvalidDefinitionException If a provider definition is invalid in the container
     * @throws NotFoundException If a provider or dependency is missing in the container
     * @throws DependencyException If a dependency cannot be resolved by the container
     * @throws Exception if a generic error occurred
     */
    public function testBootstrap(): void
    {
        $app = new Application(basePath: __DIR__ . '/fixtures/');

        $this->assertFalse($app->isBooted);
        $app->bootstrapWith([BootProviders::class]);
        $this->assertTrue($app->isBooted);
    }
}
