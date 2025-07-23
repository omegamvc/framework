<?php

/**
 * Part of Omega - Tests\Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Console\Commands;

use Omega\Console\Command;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Provides structured help data for console commands.
 *
 * This command class returns an associative array containing metadata
 * about available commands, their options, and their grouping or context.
 * It is primarily used to generate help output in the CLI interface.
 *
 * The returned structure includes:
 * - `commands`: a list of command names and their descriptions.
 * - `options`: available options for commands with corresponding help text.
 * - `relation`: defines contextual groupings (e.g. tags or categories) for commands.
 *
 * Example use case: listing available test-related commands for the "unit" test group.
 *
 * @category   Omega\Tests
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversNothing]
class RegisterHelpCommand extends Command
{
    /**
     * Print command help.
     *
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands' => [
                'test'   => 'some test will appear in test',
            ],
            'options'  => [
                '--test' => 'this also will display in test',
            ],
            'relation' => [
                'test'   => ['[unit]'],
            ],
        ];
    }
}
