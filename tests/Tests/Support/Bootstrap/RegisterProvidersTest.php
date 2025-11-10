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
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Support\Bootstrap\BootProviders;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class RegisterProvidersTest
 *
 * This test suite verifies that service providers can be correctly registered
 * and booted within the Application lifecycle. It ensures that providers added
 * at runtime are included in the boot sequence alongside default and vendor
 * providers, and that the final list of booted providers reflects all expected
 * entries.
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
#[CoversClass(AbstractServiceProvider::class)]
#[CoversClass(Application::class)]
#[CoversClass(BootProviders::class)]
class RegisterProvidersTest extends TestCase
{
    /**
     * Test bootstrap.
     *
     * @return void
     * @throws NotFoundException If a dependency required by the Application cannot be found
     * @throws DependencyException If the Application cannot resolve a required dependency
     * @throws InvalidDefinitionException If a definition registered in the container is invalid
     * @throws Exception if a generic error occurred
     */
    public function testBootstrap(): void
    {
        $app = new Application(basePath: __DIR__ . '/fixtures/');
        $app->register(TestRegisterServiceProvider::class);
        $app->bootstrapWith([BootProviders::class]);

        $this->assertCount(
            3,
            (fn () => $this->{'bootedProviders'})->call($app),
            '1 from default provider, 1 from this test, and 1 from vendor.'
        );
    }
}
