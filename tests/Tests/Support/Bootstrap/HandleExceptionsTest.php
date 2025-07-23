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

use ErrorException;
use Omega\Application\Application;
use Omega\Http\Request;
use Omega\Exceptions\ExceptionHandler;
use Omega\Support\Bootstrap\HandleExceptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

use function dirname;

use const E_ERROR;
use const E_USER_DEPRECATED;

/**
 * Test suite for the HandleExceptions class.
 *
 * This test covers the behavior of the exception and error handling system within the Omega framework.
 * It verifies the handling of:
 * - Standard PHP errors
 * - Deprecation notices
 * - Exceptions
 * - Fatal error shutdowns (partially skipped due to environment limitations)
 *
 * These tests ensure that the HandleExceptions class interacts correctly with:
 * - The Application container
 * - The configured ExceptionHandler
 * - The Request object (during exception rendering)
 *
 * NOTE: The shutdown test is currently skipped as it's difficult to simulate fatal errors reliably in unit tests.
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
#[CoversClass(Request::class)]
#[CoversClass(ExceptionHandler::class)]
#[CoversClass(HandleExceptions::class)]
class HandleExceptionsTest extends TestCase
{
    /**
     * Test it can handle error.
     *
     * @return void
     * @throws ErrorException
     */
    public function testItCanHandleError(): void
    {
        $app = new Application(dirname(__DIR__) . '/fixtures/support/bootstrap/app2');
        $app->set('environment', 'testing');

        $handle = new HandleExceptions();
        $handle->bootstrap($app);

        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage(__CLASS__);
        $handle->handleError(E_ERROR, __CLASS__, __FILE__, __LINE__);

        $app->flush();
    }

    /**
     * Test it can handle error deprecation.
     *
     * @return void
     * @throws ErrorException
     */
    public function testItCanHandleErrorDeprecation(): void
    {
        $app = new Application(dirname(__DIR__) . '/fixtures/support/bootstrap/app2');
        $app->set('environment', 'testing');
        $app->set(ExceptionHandler::class, fn() => new TestHandleExceptions($app));
        $app->set('log', fn() => new TestLog());

        $handle = new HandleExceptions();
        $handle->bootstrap($app);

        $app[ExceptionHandler::class]->deprecated();
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('deprecation');
        $handle->handleError(E_USER_DEPRECATED, 'deprecation', __FILE__, __LINE__);

        $app->flush();
    }

    /**
     * Test it can handle exception.
     *
     * @return void
     * @throws Throwable
     */
    public function testItCanHandleException(): void
    {
        $app = new Application(dirname(__DIR__) . '/fixtures/support/bootstrap/app2');
        $app->set('request', fn(): Request => new Request('/'));
        $app->set('environment', 'testing');
        $app->set(ExceptionHandler::class, fn() => new TestHandleExceptions($app));

        $handle = new HandleExceptions();
        $handle->bootstrap($app);

        try {
            throw new ErrorException('testing');
        } catch (Throwable $th) {
            $handle->handleException($th);
        }
        $app->flush();
    }

    /**
     * Test it can handle shutdown.
     *
     * @return void
     * @throws Throwable
     * /
    public function testItCanHandleShutdown(): void
    {
        $this->markTestSkipped("don't how to test, but its work");

        $app = new Application(dirname(__DIR__) . '/fixtures/support/bootstrap/app2');
        $app->set('environment', 'testing');
        $app->set(ExceptionHandler::class, fn() => new TestHandleExceptions($app));

        $handle = new HandleExceptions();
        $handle->bootstrap($app);
        $handle->handleShutdown();

        $app->flush();
    }*/
}
