<?php

/**
 * Part of Omega - Tests\Testing Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Testing;

use Omega\Application\Application;
use Omega\Http\HttpKernel;
use Omega\Testing\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use function dirname;

/**
 * Class TestCaseTest
 *
 * This test class ensures that the custom test base class {@see TestCase}
 * correctly initializes and tears down the test environment using the core {@see Application}
 * and {@see HttpKernel} components.
 *
 * It validates that the base setup functions as expected, providing a reliable foundation
 * for all application-level tests that depend on the framework's kernel and container.
 *
 * @category  Omega\Tests
 * @package   Testing
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Application::class)]
#[CoversClass(HttpKernel::class)]
class TestCaseTest extends TestCase
{
    /**
     * Set up the test environment before each test.
     *
     * This method is called before each test method is run.
     * Override it to initialize objects, mock dependencies, or reset state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Support' . DIRECTORY_SEPARATOR . 'Bootstrap' . DIRECTORY_SEPARATOR . 'RegisterProvidersTest.php';
        $this->app = new Application(dirname(__DIR__) . '/fixtures/testing/app2');
        $this->app->set(HttpKernel::class, fn () => new HttpKernel($this->app));

        parent::setUp();
    }

    /**
     * Test run smoothly.
     *
     * @return void
     */
    public function testRunSmoothly(): void
    {
        $this->assertTrue(true);
    }
}
