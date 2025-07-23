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

namespace Tests\Support\Facades;

use Omega\Collection\Collection;
use Omega\Application\Application;
use Omega\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tests\Support\Facades\Sample\FacadesTestClass;
use Throwable;

/**
 * Class FacadeTest
 *
 * Unit tests for the Facade base functionality.
 *
 * Tests include:
 * - Verifying static calls proxy correctly to the underlying service.
 * - Ensuring an exception is thrown when the Facade is used without an Application instance.
 *
 * @category   Omega\Tests
 * @package    Support
 * @subpackage Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Application::class)]
#[CoversClass(Collection::class)]
#[CoversClass(Facade::class)]
class FacadeTest extends TestCase
{
    /**
     * Test it can call static.
     *
     * @return void
     */
    public function testItCanCallStatic(): void
    {
        $app = new Application(__DIR__);
        $app->set(Collection::class, fn () => new Collection(['php' => 'great']));

        Facade::setFacadeBase($app);
        require_once __DIR__ . DIRECTORY_SEPARATOR . 'Sample' . DIRECTORY_SEPARATOR . 'FacadesTestClass.php';

        $this->assertTrue(FacadesTestClass::has('php'));
        $app->flush();
        Facade::flushInstance();
    }

    /**
     * Test it throw error when application not set.
     *
     * @return void
     */
    public function testItThrowErrorWhenApplicationNotSet(): void
    {
        require_once __DIR__ . '/Sample/FacadesTestClass.php';

        Facade::flushInstance();
        Facade::setFacadeBase();
        try {
            FacadesTestClass::has('php');
        } catch (Throwable $th) {
            $this->assertEquals('Call to a member function make() on null', $th->getMessage());
        }
    }
}
