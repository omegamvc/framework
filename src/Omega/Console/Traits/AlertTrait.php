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

namespace Omega\Console\Traits;

use Omega\Console\Style\Decorate;
use Omega\Console\Style\Style;

/**
 * Trait AlertTrait
 *
 * Provides formatted alert messages (info, warning, fail, ok) using the Style builder.
 * Supports customizable left margin padding for aligning messages visually in the terminal.
 *
 * Example usage:
 * ```php
 * $this->marginLeft(4)->warn('Something might go wrong.');
 * ```
 * @category   Omega
 * @package    Console
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
trait AlertTrait
{
    /** @var int Number of spaces to prepend to each alert (left margin). */
    protected int $marginLeft = 0;

    /**
     * Set the left margin (number of spaces) for alert messages.
     *
     * @param int $marginLeft Number of spaces to prepend.
     * @return static
     */
    public function marginLeft(int $marginLeft): static
    {
        $this->marginLeft = $marginLeft;

        return $this;
    }

    /**
     * Render a styled "info" alert message.
     *
     * @param string $info The message content.
     * @return Style The rendered styled message.
     */
    public function info(string $info): Style
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' info ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgBlue()
            ->push(' ')
            ->push($info)
            ->newLines(2);
    }

    /**
     * Render a styled "warn" alert message.
     *
     * @param string $warn The warning content.
     * @return Style The rendered styled message.
     */
    public function warn(string $warn): Style
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' warn ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgYellow()
            ->push(' ')
            ->push($warn)
            ->newLines(2);
    }

    /**
     * Render a styled "fail" alert message.
     *
     * @param string $fail The failure content.
     * @return Style The rendered styled message.
     */
    public function fail(string $fail): Style
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' fail ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgRed()
            ->push(' ')
            ->push($fail)
            ->newLines(2);
    }

    /**
     * Render a styled "ok" alert message, typically used to indicate success.
     *
     * @param string $ok The success content.
     * @return Style The rendered styled message.
     */
    public function ok(string $ok): Style
    {
        return (new Style())
            ->newLines()
            ->repeat(' ', $this->marginLeft)
            ->push(' ok ')
            ->bold()
            ->rawReset([Decorate::RESET_BOLD_DIM])
            ->bgGreen()
            ->push(' ')
            ->push($ok)
            ->newLines(2);
    }
}
