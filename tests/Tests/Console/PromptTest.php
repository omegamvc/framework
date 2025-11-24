<?php

declare(strict_types=1);

namespace Tests\Console;

use PHPUnit\Framework\TestCase;
use Omega\Text\Str;
use function fclose;
use function function_exists;
use function fwrite;
use function proc_close;
use function proc_open;
use function stream_get_contents;
use const DIRECTORY_SEPARATOR;

final class PromptTest extends TestCase
{
    private function runCommand($command, $input): false|string
    {
        $descriptors = [
            0 => ['pipe', 'r'], // input
            1 => ['pipe', 'w'], // output
            2 => ['pipe', 'w'], // errors
        ];

        $process = proc_open($command, $descriptors, $pipes);

        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($process);

        return $output;
    }

    public function testOptionPrompt(): void
    {
        $input  = 'test_1';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'option';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'ok'));
    }

    public function testOptionPromptDefault(): void
    {
        $input  = 'test_2';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'option';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'default'));
    }

    public function testSelectPrompt(): void
    {
        $input  = '1';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'select';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'ok'));
    }

    public function testSelectPromptDefault(): void
    {
        $input  = 'rz';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'select';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'default'));
    }

    public function testTextPrompt(): void
    {
        $input  = 'text';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'text';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'text'));
    }

    public function testAnyKeyPrompt(): void
    {
        if (!function_exists('readline_callback_handler_install')) {
            $this->markTestSkipped("Console doest support 'readline_callback_handler_install'");
        }

        $input  = 'f';
        $cli    = __DIR__ . DIRECTORY_SEPARATOR . 'Assets' . DIRECTORY_SEPARATOR . 'any';
        $output = $this->runCommand('php "' . $cli . '"', $input);

        $this->assertTrue(Str::contains($output, 'you press f'));
    }
}
