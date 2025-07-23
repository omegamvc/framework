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

use Omega\Console\CommandMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Unit tests for the CommandMap class.
 *
 * This test suite verifies the correct behavior of the CommandMap configuration object,
 * which is responsible for mapping CLI command definitions (e.g., command name, mode,
 * class handler, function callback, and pattern matching) to callable logic.
 *
 * Covered functionalities:
 * - Retrieval of `cmd`, `mode`, `class`, and `fn` values
 * - Default fallbacks for `mode` and `fn`
 * - Proper resolution and validation of class/function pairs
 * - Matching logic using `pattern`, `cmd`, or custom `match` callbacks
 * - Callable resolution using either `class` + `fn` or explicit `fn` definitions
 *
 * @category  Omega\Tests
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(CommandMap::class)]
class CommandMapTest extends TestCase
{
    /**
     * Test it can get cmd.
     *
     * @return void
     */
    public function testItCanGetCmd(): void
    {
        $command = new CommandMap([
            'cmd' => 'test:test',
        ]);

        $this->assertEquals(['test:test'], $command->cmd());
    }

    /**
     * Test it can get mode.
     *
     * @return void
     */
    public function testICanGetMode(): void
    {
        $command = new CommandMap([
            'cmd'  => 'test:test',
            'mode' => 'full',
        ]);

        $this->assertEquals('full', $command->mode());
    }

    /**
     * Test it can get mode default.
     *
     * @return void
     */
    public function testICanGetModeDefault(): void
    {
        $command = new CommandMap([]);

        $this->assertEquals('full', $command->mode());
    }

    /**
     * Test it can get class.
     *
     * @return void
     */
    public function testICanGetClass(): void
    {
        $command = new CommandMap([
            'class' => 'test-class',
        ]);

        $this->assertEquals('test-class', $command->class());
    }

    /**
     * Test it can get class using fn.
     *
     * @return void
     */
    public function testICanGetClassUsingFn(): void
    {
        $command = new CommandMap([
            'fn' => ['test-class', 'main'],
        ]);

        $this->assertEquals('test-class', $command->class());
    }

    /**
     * Test it will throw error when fn is array but class not exists.
     *
     * @return void
     */
    public function testIWillThrowErrorWhenFnIsArrayButClassNotExist(): void
    {
        $command = new CommandMap([
            'fn' => [],
        ]);

        try {
            $command->class();
        } catch (Throwable $th) {
            $this->assertEquals('Command map require class in (class or fn).', $th->getMessage());
        }
    }

    /**
     * Test it will throw error when class not exists.
     *
     * @return void
     */
    public function testIWillThrowErrorWhenClassNotExist(): void
    {
        $command = new CommandMap([]);

        try {
            $command->class();
        } catch (Throwable $th) {
            $this->assertEquals('Command map require class in (class or fn).', $th->getMessage());
        }
    }

    /**
     * Test it can get fn.
     *
     * @return void
     */
    public function testICanGetFn(): void
    {
        $command = new CommandMap([
            'fn' => ['test-class', 'main'],
        ]);

        $this->assertEquals(['test-class', 'main'], $command->fn());
    }

    /**
     * Test it can get fn default.
     *
     * @return void
     */
    public function testICanGetFnDefault(): void
    {
        $command = new CommandMap([]);

        $this->assertEquals('main', $command->fn());
    }

    /**
     * Test it can get default option.
     * @return void
     */
    public function testICanGetDefaultOption(): void
    {
        $command = new CommandMap([]);

        $this->assertEquals('main', $command->fn());
    }

    /**
     * Test it can match callback using pattern.
     *
     * @return void
     */
    public function testICanMatchCallbackUsingPattern(): void
    {
        $command = new CommandMap([
            'pattern' => 'test:test',
        ]);

        $this->assertTrue(($command->match())('test:test'));
    }

    /**
     * Test it can match callback using match.
     *
     * @return void
     */
    public function testICanMatchCallbackUsingMatch(): void
    {
        $command = new CommandMap([
            'match' => fn ($given) => true,
        ]);

        $this->assertTrue(($command->match())('always_true'));
    }

    /**
     * Test it can match callback using cmd full.
     *
     * @return void
     */
    public function testICanMatchCallbackUsingCmdFull(): void
    {
        $command = new CommandMap([
            'cmd' => ['test:test', 'test:start'],
        ]);

        $this->assertTrue(($command->match())('test:test'));
    }

    /**
     * Test it can match callback using cmd start,
     *
     * @return void
     */
    public function testICanMatchCallbackUsingCmdStart(): void
    {
        $command = new CommandMap([
            'cmd'  => ['make:', 'test:'],
            'mode' => 'start',
        ]);

        $this->assertTrue(($command->match())('test:unit'));
    }

    /**
     * Test it can call is match.
     *
     * @return void
     */
    public function testICanCallIsMatch(): void
    {
        $command = new CommandMap([
            'cmd'  => 'test:unit',
        ]);

        $this->assertTrue($command->isMatch('test:unit'));
    }

    /**
     * Test it can get call using fn.
     *
     * @return void
     */
    public function testICanGetCallUsingFn(): void
    {
        $command = new CommandMap([
            'fn'  => ['some-class', 'main'],
        ]);

        $this->assertEquals(['some-class', 'main'], $command->call());
    }

    /**
     * Test it can get call using class.
     *
     * @return void
     */
    public function testICanGetCallUsingClass(): void
    {
        $command = new CommandMap([
            'class' => 'some-class',
            // skip 'fn' because default if 'main'
        ]);

        $this->assertEquals(['some-class', 'main'], $command->call());
    }
}
