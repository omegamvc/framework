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
use Omega\Console\Traits\CommandTrait;

/**
 * Class CommandTraitStub
 *
 * A lightweight stub class used for testing features provided by the CommandTrait.
 * This class is designed to simulate a console command environment without relying
 * on a fully functional Command class, allowing isolated and focused testing
 * of trait-specific logic such as input parsing, output formatting, and argument handling.
 *
 * It can be extended or instantiated anonymously in unit tests to verify methods
 * like `textGreen()`, `main()`, or any utility included in the trait.
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
class CommandTraitStub extends Command
{
    use CommandTrait;
}
