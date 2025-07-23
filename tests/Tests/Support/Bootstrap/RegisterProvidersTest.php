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
use Omega\Support\Bootstrap\BootProviders;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class RegisterProvidersTest
 *
 * Unit test for verifying the application service provider registration and bootstrapping process.
 *
 * This test ensures that the `BootProviders` bootstrapper correctly registers and boots service
 * providers, including:
 * - Default providers automatically loaded by the application
 * - Custom providers explicitly registered in the test
 * - Third-party providers loaded via composer
 *
 * The test case also leverages reflection to inspect the internal list of booted providers.
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
#[CoversClass(BootProviders::class)]
class RegisterProvidersTest extends TestCase
{
    /**
     * Test bootstrap.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanBootstrap(): void
    {
        $app = new Application(dirname(__DIR__, 2) . '/fixtures/support/bootstrap/app2/');
        $app->register(TestRegisterServiceProvider::class);
        $app->bootstrapWith([BootProviders::class]);

        $this->assertCount(3, (fn () => $this->{'bootedProviders'})->call($app), '1 from default provider, 1 from this test, and 1 from vendor.');
    }
}
