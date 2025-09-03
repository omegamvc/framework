<?php

declare(strict_types=1);

namespace Omega\Console\IO;

use function defined;
use function fopen;
use function function_exists;
use function getenv;
use function implode;
use function stripos;

use const PHP_OS;
use const STDERR;

/**
 */
class ConsoleOutputStream extends OutputStream implements OutputStreamInterface
{
    public function __construct()
    {
        parent::__construct($this->openErrorStream());
    }

    /**
     * Checks whether the stream is interactive (connected to a terminal).
     */
    public function isInteractive(): bool
    {
        return false === $this->isRunningOS400();
    }

    /**
     * @return resource
     */
    private function openErrorStream()
    {
        if (false === $this->isInteractive()) {
            return fopen('php://output', 'w');
        }

        return defined('STDERR') ? STDERR : (@fopen('php://stderr', 'w') ?: fopen('php://output', 'w'));
    }

    /**
     * Checks if current executing environment is IBM iSeries (OS400), which
     * doesn't properly convert character-encodings between ASCII to EBCDIC.
     */
    private function isRunningOS400(): bool
    {
        $checks = [
            function_exists('php_uname') ? php_uname('s') : '',
            getenv('OSTYPE'),
            PHP_OS,
        ];

        return false !== stripos(implode(';', $checks), 'OS400');
    }
}
