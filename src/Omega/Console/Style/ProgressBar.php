<?php

declare(strict_types=1);

namespace Omega\Console\Style;

use Omega\Console\Traits\CommandTrait;

use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function call_user_func;
use function ceil;
use function str_pad;
use function str_repeat;
use function str_replace;

use const PHP_EOL;

class ProgressBar
{
    use CommandTrait;

    /** @var string  */
    private string $template;

    /** @var int  */
    public int $current = 0;

    /** @var int  */
    public int $mask    = 1;

    /** @var string  */
    private string $progress;

    /** @var callable(): string Callback when task was complete. */
    public $complete;

    /** @var array<callable(int, int): string> Bind template. */
    private array $binds;

    /** @var array<callable(int, int): string> Bind custom template. */
    public static array $customBinds = [];

    /**
     * @param string $template
     * @param array<callable(int, int): string> $binds
     */
    public function __construct(string $template = ':progress :percent', array $binds = [])
    {
        $this->progress = '';
        $this->template = $template;
        $this->complete = fn (): string => $this->complete();

        $this->binding($binds);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $bindsValues = array_map(
            fn($bind) => $bind($this->current, $this->mask),
            $this->binds
        );

        return str_replace(array_keys($this->binds), $bindsValues, $this->template);
    }

    /**
     * @return void
     */
    public function tick(): void
    {
        $this->progress = (string) $this;
        new Style()->replace($this->progress);

        if ($this->current + 1 > $this->mask) {
            $complete = (string) call_user_func($this->complete);
            new Style()->clear();
            new Style()->replace($complete . PHP_EOL);
        }
    }

    /**
     * Customize tick in progressbar.
     *
     * @param string $template
     * @param array<callable(int, int): string> $binds
     * @return void
     */
    public function tickWith(string $template = ':progress :percent', array $binds = []): void
    {
        $this->template = $template;
        $this->binding($binds);
        $this->progress = (string) $this;
        new Style()->replace($this->progress);

        if ($this->current + 1 > $this->mask) {
            $complete = (string) call_user_func($this->complete);
            new Style()->clear();
            new Style()->replace($complete . PHP_EOL);
        }
    }

    /**
     * @param int $current
     * @param int $maks
     * @return string
     */
    private function progress(int $current, int $maks): string
    {
        $length = 20;
        $tick   = (int) ceil($current * ($length / $maks)) - 1;
        $head   = $current === $maks ? '=' : '>';
        $bar    = str_repeat('=', $tick) . $head;
        $left   = '-';

        return '[' . str_pad($bar, $length, $left) . ']';
    }

    /**
     * Binding.
     *
     * @param array<callable(int, int): string> $binds
     * @return void
     */
    public function binding(array $binds): void
    {
        $binds = array_merge($binds, self::$customBinds);
        if (false === array_key_exists(':progress', $binds)) {
            $binds[':progress'] =  fn ($current, $maks): string => $this->progress($current, $maks);
        }

        if (false === array_key_exists(':percent', $binds)) {
            $binds[':percent'] =  fn ($current, $maks): string => ceil(($current / $maks) * 100) . '%';
        }
        $this->binds    = $binds;
    }

    /**
     * @return string
     */
    private function complete(): string
    {
        return $this->progress;
    }
}
