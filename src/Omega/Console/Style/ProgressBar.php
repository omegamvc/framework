<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Console\Style;

use Omega\Console\Traits\CommandTrait;

use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function call_user_func;
use function call_user_func_array;
use function ceil;
use function str_pad;
use function str_repeat;
use function str_replace;

use const PHP_EOL;

/**
 * Class ProgressBar
 *
 * Provides a customizable progress bar for terminal output.
 * Allows dynamic binding of placeholders in a string template using callable renderers.
 * Supports automatic refresh and final output when the progress is complete.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Style
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class ProgressBar
{
    use CommandTrait;

    /**
     * The string template used to render the progress bar.
     * Placeholders in the format `:placeholder` will be replaced by bound callbacks.
     *
     * @var string
     */
    private string $template;

    /** @var int The current progress value. */
    public int $current = 0;

    /** @var int The maximum value of the progress bar. */
    public int $maks = 1;

    /** @var string The rendered progress bar string. */
    private string $progress;

    /** @var callable(): string Callback executed when the progress reaches completion. */
    public $complete;

    /** @var array<callable(int, int): string> Bound callbacks used to replace placeholders in the template. */
    private array $binds;

    /** @var array<callable(int, int): string> Global user-defined bindings for customizing placeholder behavior. */
    public static array $customBinds = [];

    /**
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
     * Converts the progress bar to a string by replacing all bound placeholders.
     *
     * @return string
     */
    public function __toString()
    {
        $binds = array_map(function ($bind) {
            return call_user_func_array($bind, [
                $this->current,
                $this->maks,
            ]);
        }, $this->binds);

        return str_replace(array_keys($binds), $binds, $this->template);
    }

    /**
     * Advances the progress bar and updates the display.
     * If the maximum value is reached, the completion callback is triggered.
     *
     * @return void
     */
    public function tick(): void
    {
        $this->progress = (string) $this;
        (new Style())->replace($this->progress);

        if ($this->current + 1 > $this->maks) {
            $complete = (string) call_user_func($this->complete);
            (new Style())->clear();
            (new Style())->replace($complete . PHP_EOL);
        }
    }

    /**
     * Sets a new template and bindings, and advances the progress bar.
     *
     * @param string $template The new template for rendering.
     * @param array<callable(int, int): string> $binds Custom bindings for placeholders.
     */
    public function tickWith(string $template = ':progress :percent', array $binds = []): void
    {
        $this->template = $template;
        $this->binding($binds);
        $this->progress = (string) $this;
        (new Style())->replace($this->progress);

        if ($this->current + 1 > $this->maks) {
            $complete = (string) call_user_func($this->complete);
            (new Style())->clear();
            (new Style())->replace($complete . PHP_EOL);
        }
    }

    /**
     * Binds placeholder names to their rendering logic.
     * Default bindings are added if not already provided.
     *
     * @param array<callable(int, int): string> $binds The bindings to apply.
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
     * Returns the final progress bar state used at completion.
     *
     * @return string
     */
    private function complete(): string
    {
        return $this->progress;
    }

    /**
     * Default logic for rendering the :progress placeholder.
     *
     * @param int $current The current progress value.
     * @param int $maks The maximum progress value.
     * @return string The rendered progress bar.
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
}
