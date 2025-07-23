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

use Omega\Exceptions\ExceptionHandler;
use PHPUnit\Framework\Assert;
use Throwable;

/**
 * Custom exception handler used in test scenarios.
 *
 * This class overrides the report method to assert a specific
 * exception message, allowing validation of error flows in tests.
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
class TestHandleExceptions extends ExceptionHandler
{
    /**
     * Overrides the default report logic with a test assertion.
     *
     * @param Throwable $th The throwable being reported.
     *
     * @return void
     */
    public function report(Throwable $th): void
    {
        Assert::assertTrue($th->getMessage() === 'testing', 'testing helper');
    }

    /**
     * Stub method to simulate a deprecated call.
     *
     * @deprecated This method is deprecated and used only for testing.
     *
     * @return void
     */
    public function deprecated(): void
    {
    }
}
