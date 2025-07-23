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

namespace Tests\Console;

use Omega\Console\Command;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

use function explode;

/**
 * Test suite for parsing command-line arguments using the Omega\Console\Command class.
 *
 * This test class validates how the Command class parses an array of CLI arguments
 * passed via $argv. It ensures correct extraction of command names and options,
 * proper immutability behavior when attempting to modify or unset parameters,
 * and internal option lookup via protected method invocation.
 *
 * It directly contributes to code coverage for the Command class.
 *
 * @category  Omega\Tests
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Command::class)]
class ConsoleParseAsArrayTest extends TestCase
{
    /**
     * Test it can parse normal command with space.
     *
     * @return void
     */
    public function testItCanParseNormalCommandWithSpace(): void
    {
        $command = 'php omega test --n john -tests -s --who-is children';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        $this->assertEquals(
            'test',
            $cli['name'],
            'valid parse name: test'
        );

        $this->assertEquals(
            'john',
            $cli['n'],
            'valid parse from short param with spare space: --n'
        );

        $this->assertTrue(
            isset($cli['who-is']),
            'valid parse from long param: --who-is'
        );
    }

    /**
     * Test it will throw exception when change command.
     *
     * @return void
     */
    public function testItWillTrowExceptionWhenChangeCommand(): void
    {
        $command = 'php omega test --n john -tests -s --who-is children';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        try {
            $cli['name'] = 'taylor';
        } catch (Throwable $th) {
            $this->assertEquals('Command cant be modify', $th->getMessage());
        }
    }

    /**
     * Test it will throw exception when unset command.
     *
     * @return void
     */
    public function testItWillThrowExceptionWhenUnsetCommand(): void
    {
        $command = 'php omega test --n john -tests -s --who-is children';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        try {
            unset($cli['name']);
        } catch (Throwable $th) {
            $this->assertEquals('Command cant be modify', $th->getMessage());
        }
    }

    /**
     * Test it can check option has exit or not.
     *
     * @return void
     */
    public function testItCanCheckOptionHasExitOrNot(): void
    {
        $command = 'php omega test --true="false"';
        $argv = explode(' ', $command);
        $cli = new Command($argv);

        $this->assertTrue((fn() => $this->{'hasOption'}('true'))->call($cli));
        $this->assertFalse((fn() => $this->{'hasOption'}('not-exist'))->call($cli));
    }
}
