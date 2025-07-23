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

use Omega\Text\Str;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function dirname;
use function fclose;
use function function_exists;
use function fwrite;
use function proc_close;
use function proc_open;
use function stream_get_contents;

/**
 * Class PromptTest
 *
 * This class contains PHPUnit tests for various console prompt interactions.
 * It runs external PHP scripts simulating different prompt types and verifies
 * their output.
 *
 * @category  Omega\Tests
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversNothing]
class PromptTest extends TestCase
{
    /**
     * Executes a console command with given input and returns its output.
     *
     * @param string $command The full command line string to execute.
     * @param string $input   The input string to be written to the command's stdin.
     * @return false|string Returns the command's stdout output as a string,
     *                     or false on failure.
     */
    private function runCommand(string $command, string $input): false|string
    {
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes);

        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        //$errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($process);

        return $output;
    }

    /**
     * Test option prompt.
     *
     * @return void
     */
    public function testOptionPrompt(): void
    {
        $input = 'test_1';
        $cli = dirname(__DIR__) . '/fixtures/console/prompt/option';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'ok'));
    }

    /**
     * Test option prompt default.
     *
     * @return void
     */
    public function testOptionPromptDefault(): void
    {
        $input = 'test_2';
        $cli = dirname(__DIR__) . '/fixtures/console/prompt/option';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'default'));
    }

    /**
     * Test select prompt.
     *
     * @return void
     */
    public function testSelectPrompt(): void
    {
        $input = '1';
        $cli = dirname(__DIR__) . '/fixtures/console/prompt/select';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'ok'));
    }

    /**
     * Test select prompt default.
     *
     * @return void
     */
    public function testSelectPromptDefault(): void
    {
        $input = 'rz';
        $cli = dirname(__DIR__) . '/fixtures/console/prompt/select';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'default'));
    }

    /**
     * Test text prompt.
     *
     * @return void
     */
    public function testTextPrompt(): void
    {
        $input = 'text';
        $cli = dirname(__DIR__) . '/fixtures/console/prompt/text';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'text'));
    }

    /**
     * Test any key prompt.
     *
     * @return void
     */
    public function testAnyKeyPrompt(): void
    {
        if (!function_exists('readline_callback_handler_install')) {
            $this->markTestSkipped("Console doest support 'readline_callback_handler_install'");
        }

        $input = 'f';
        $cli = dirname(__DIR__) . '/fixtures/console/prompt/any';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'you press f'));
    }
}
