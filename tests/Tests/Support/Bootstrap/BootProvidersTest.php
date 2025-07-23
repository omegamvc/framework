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

use function dirname;

/**
 * Test suite for the BootProviders bootstrap process.
 *
 * This test ensures that the application correctly boots
 * when the BootProviders class is invoked during the bootstrap phase.
 * It verifies the transition from an unbooted to a booted state.
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
#[CoversClass(BootProviders::class)]
class BootProvidersTest extends TestCase
{
    /**
     * Test bootstrap.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testBootstrap(): void
    {
        $app = new Application(dirname(__DIR__, 2) . '/fixtures/support/bootstrap/app/');

        $this->assertFalse($app->isBooted());
        $app->bootstrapWith([BootProviders::class]);
        $this->assertTrue($app->isBooted());
    }
}
