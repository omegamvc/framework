<?php

/**
 * Part of Omega - Console Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Console\Test;

use Omega\Console\AbstractCommand;

/**
 * Test Command class.
 *
 * A minimal concrete implementation of {@see AbstractCommand} used exclusively
 * for testing purposes.
 *
 * This class exists to allow instantiation of a command object in test suites,
 * since {@see AbstractCommand} is abstract and cannot be instantiated directly.
 *
 * No additional behavior or logic is provided here; the class serves only as a
 * lightweight stand-in to validate the behavior of components that depend on a
 * concrete command instance.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Test
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class TestCommand extends AbstractCommand
{
}
