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

use PHPUnit\Framework\Assert;

/**
 * A test logger class to validate log entries.
 *
 * This mock logger is designed to assert specific log levels
 * and messages during tests, especially for deprecation warnings.
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
class TestLog
{
    /**
     * Logs a message and asserts the expected level and content.
     *
     * @param int    $level   The log level (e.g. Logger::WARNING).
     * @param string $message The log message.
     *
     * @return void
     */
    public function log(int $level, string $message): void
    {
        Assert::assertEquals($level, 16384);
        Assert::assertEquals($message, 'deprecation');
    }
}
