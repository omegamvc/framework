<?php

/**
 * Part of Omega - Command Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Console\Stubs;

use Omega\Console\Command;
use Omega\Console\Traits\ValidateCommandTrait;

/**
 * Stub class to test the ValidateCommandTrait functionality.
 *
 * This class is used in unit tests to isolate and verify the behavior
 * of the ValidateCommandTrait without involving additional logic from
 * real command implementations.
 *
 * It extends the base Command class and uses the ValidateCommandTrait
 * to provide a controlled environment for testing validation logic.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Stubs
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @internal This class is meant for internal use only and may change without notice.
 */
class ValidateCommandTraitStub extends Command
{
    use ValidateCommandTrait;
}
