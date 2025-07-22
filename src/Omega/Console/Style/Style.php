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

use Closure;
use Omega\Console\Output\OutputStream;
use Omega\Console\Style\Color\RuleInterface;
use Omega\Console\Style\Color\BackgroundColor;
use Omega\Console\Style\Color\ForegroundColor;
use Omega\Console\Traits\CommandTrait;
use Omega\Text\Str;

use function method_exists;
use function Omega\Text\text;
use function preg_replace;
use function str_repeat;
use function strlen;
use function strtolower;

/**
 * Represents a styled text segment with formatting rules for CLI output.
 *
 * This class manages styling information such as text decorations,
 * foreground and background colors, and additional formatting options.
 * It is primarily used for rendering stylized command-line output.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Style
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @method self textRed()
 * @method self textYellow()
 * @method self textBlue()
 * @method self textGreen()
 * @method self textDim()
 * @method self textMagenta()
 * @method self textCyan()
 * @method self textLightGray()
 * @method self textDarkGray()
 * @method self textLightGreen()
 * @method self textLightYellow()
 * @method self textLightBlue()
 * @method self textLightMagenta()
 * @method self textLightCyan()
 * @method self textWhite()
 * @method self bgRed()
 * @method self bgYellow()
 * @method self bgBlue()
 * @method self bgGreen()
 * @method self bgMagenta()
 * @method self bgCyan()
 * @method self bgLightGray()
 * @method self bgDarkGray()
 * @method self bgLightGreen()
 * @method self bgLightYellow()
 * @method self bgLightBlue()
 * @method self bgLightMagenta()
 * @method self bgLightCyan()
 * @method self bgWhite()
 * @method text_red_500()
 * @method bg_blue_500()
 * @method text_red_10()
 */
class Style
{
    use CommandTrait;

    /** @var array<int, array<int, string|int>> Raw formatting rules for the text. */
    private array $rawRules = [];

    /** @var array<int, int> Reset rules to clear previous styles before applying new ones. */
    private array $resetRules = [Decorate::RESET];

    /** @var array<int, int> Foreground color rule for the text. */
    private array $textColorRule = [Decorate::TEXT_DEFAULT];

    /** @var array<int, int> Background color rule for the text. */
    private array $bgColorRule = [Decorate::BG_DEFAULT];

    /** @var array<int, int> Decoration rules such as bold, underline, italic, etc. */
    private array $decorateRules = [];

    /** @var string The actual text to be styled. */
    private string $text;

    /** @var int The length of the text string. */
    private int $length;

    /** @var string Optional prefix or reference string used for tracking or comparison. */
    private string $ref = '';

    /** @var OutputStream|null Optional stream to which the styled output is sent. */
    private ?OutputStream $outputStream = null;

    /**
     * Constructor to initialize the style with an optional text value.
     *
     * @param string $text Text to be decorated (optional).
     */
    public function __construct(string $text = '')
    {
        $this->text   = $text;
        $this->length = strlen($text);
    }

    /**
     * Allows the object to be called as a function to apply styling to new text.
     *
     * @param string $text Text to be decorated.
     * @return self Returns the instance with the new text applied and flushed.
     */
    public function __invoke(string $text): self
    {
        $this->text   = $text;
        $this->length = strlen($text);

        return $this->flush();
    }

    /**
     * Returns the styled text as a string for terminal output.
     *
     * @return string The formatted string with applied styles.
     */
    public function __toString(): string
    {
        return $this->toString($this->text, $this->ref);
    }

    /**
     * Magic method to handle dynamic method calls for colors and decorations.
     *
     * Dynamically applies text or background color based on method name
     * or calls trait methods if available.
     *
     * @param string            $name Method name being called.
     * @param array<int, mixed> $arguments Arguments passed to the method.
     * @return self Returns the instance with applied style rules.
     */
    public function __call(string $name, array $arguments): self
    {
        if (method_exists($this, $name)) {
            $constant = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));

            if (Str::startsWith($name, 'text')) {
                $constant              = 'TEXT' . text($constant)->upper()->slice(4);
                $this->textColorRule = [Decorate::getConstant($constant)];
            }

            if (Str::startsWith($name, 'bg')) {
                $constant            =  'BG' . text($constant)->upper()->slice(2);
                $this->bgColorRule = [Decorate::getConstant($constant)];
            }

            return $this;
        }

        $constant = text($name)->upper();
        if ($constant->startsWith('TEXT_')) {
            $constant->slice(5);
            $this->textColor(Colors::hexText(ColorVariant::getConstant($constant->__toString())));
        }

        if ($constant->startsWith('BG_')) {
            $constant->slice(3);
            $this->bgColor(Colors::hexBg(ColorVariant::getConstant($constant->__toString())));
        }

        return $this;
    }

    /**
     * Builds and returns the styled string with ANSI escape sequences.
     *
     * @param string $text Text to be rendered with styles.
     * @param string $ref  Optional prefix to be prepended to the text.
     * @return string Fully formatted string ready for output.
     */
    public function toString(string $text, string $ref = ''): string
    {
        if ($text === '' && $ref === '') {
            return '';
        }

        $rules = [];

        foreach ($this->textColorRule as $textColor) {
            $rules[] = $textColor;
        }

        foreach ($this->bgColorRule as $bgColor) {
            $rules[] = $bgColor;
        }

        foreach ($this->decorateRules as $decorate) {
            $rules[] = $decorate;
        }

        foreach ($this->rawRules as $raws) {
            foreach ($raws as $raw) {
                $rules[] = $raw;
            }
        }

        return $ref . $this->rules($rules, $text, true, $this->resetRules);
    }

    /**
     * Resets all style rules to their default values.
     *
     * @return self Returns the instance with all rules flushed.
     */
    public function flush(): self
    {
        $this->textColorRule = [Decorate::TEXT_DEFAULT];
        $this->bgColorRule   = [Decorate::BG_DEFAULT];
        $this->decorateRules = [];
        $this->resetRules    = [Decorate::RESET];
        $this->rawRules      = [];
        $this->ref           = '';

        return $this;
    }

    /**
     * Sets a reference (prefix) to be prepended before the styled text.
     *
     * @param string $textReference The reference string.
     * @return self Returns the instance with the reference set.
     */
    private function ref(string $textReference): self
    {
        $this->ref = $textReference;

        return $this;
    }

    /**
     * Chains another text block to the current style sequence.
     *
     * The previous styled text is stored as a reference, and the new text is appended.
     *
     * @param string $text New text to chain.
     * @return self Returns the instance with the chained style.
     */
    public function push(string $text): self
    {
        $ref        = $this->toString($this->text, $this->ref);
        $this->text = $text;
        $this->length += strlen($text);

        return $this->flush()->ref($ref);
    }

    /**
     * Appends a separate Style instance to the current one.
     *
     * Merges the rules and text of the given Style into the current one.
     *
     * @param Style $style Style instance to merge.
     * @return self Returns the combined styled instance.
     */
    public function tap(Style $style): self
    {
        $this->ref           = $this->toString($this->text, $this->ref) . $style->toString($style->ref);
        $this->text          = $style->text;
        $this->textColorRule = $style->textColorRule;
        $this->bgColorRule   = $style->bgColorRule;
        $this->decorateRules = $style->decorateRules;
        $this->resetRules    = $style->resetRules;
        $this->rawRules      = $style->rawRules;

        $this->length += $style->length;

        return $this;
    }

    /**
     * Returns the length of the text (excluding style codes).
     *
     * @return int Length of the plain text.
     */
    public function length(): int
    {
        return $this->length;
    }

    /**
     * Print the decorated text to the terminal.
     *
     * @param bool $newLine Whether to add a new line at the end of the output
     * @return void
     */
    public function out(bool $newLine = true): void
    {
        $out = $this . ($newLine ? PHP_EOL : null);

        echo $out;
    }

    /**
     * Print the decorated text if the condition is true.
     *
     * @param Closure|bool $condition A boolean or closure that determines if the text should be printed
     * @param bool $newLine Whether to add a new line at the end of the output
     * @return void
     */
    public function outIf(Closure|bool $condition, bool $newLine = true): void
    {
        if ($condition) {
            $out = $this . ($newLine ? PHP_EOL : null);

            echo $out;
        }
    }

    /**
     * Print the decorated text and reset the current style.
     *
     * @return self
     */
    public function yield(): self
    {
        echo $this;
        $this->text   = '';
        $this->length = 0;
        $this->flush();

        return $this;
    }

    /**
     * Write the decorated text to the output stream.
     *
     * @param bool $newLine Whether to add a new line at the end of the output
     * @return void
     */
    public function write(bool $newLine = true): void
    {
        $out = $this . ($newLine ? PHP_EOL : null);

        $this->outputStream?->write($out);
    }

    /**
     * Clear the specified terminal line (text content is preserved).
     *
     * @param int $line The line index to clear (default: current line)
     * @return void
     */
    public function clear(int $line = -1): void
    {
        $this->clearLine($line);
    }

    /**
     * Replace the specified terminal line with the given text (original content is preserved internally).
     *
     * @param string $text The new text to display
     * @param int    $line The line index to replace (default: current line)
     * @return void
     */
    public function replace(string $text, int $line = -1): void
    {
        $this->replaceLine($text, $line);
    }

    /**
     * Set the output stream to which the decorated text should be written.
     *
     * @param OutputStream $outputStream The output stream instance
     * @return self
     */
    public function setOutputStream(OutputStream $outputStream): self
    {
        $this->outputStream = $outputStream;

        return $this;
    }

    /**
     * Reset all applied decorations (sets the default reset rule).
     *
     * @return self
     */
    public function resetDecorate(): self
    {
        $this->resetRules = [Decorate::RESET];

        return $this;
    }

    /**
     * Apply bold decoration to the text.
     *
     * @return self
     */
    public function bold(): self
    {
        $this->decorateRules[] = Decorate::BOLD;
        $this->resetRules[]    = Decorate::RESET_BOLD;

        return $this;
    }

    /**
     * Apply underline decoration to the text.
     *
     * @return self
     */
    public function underline(): self
    {
        $this->decorateRules[] = Decorate::UNDERLINE;
        $this->resetRules[]    = Decorate::RESET_UNDERLINE;

        return $this;
    }

    /**
     * Apply blink decoration to the text.
     *
     * @return self
     */
    public function blink(): self
    {
        $this->decorateRules[] = Decorate::BLINK;
        $this->resetRules[]    = Decorate::RESET_BLINK;

        return $this;
    }

    /**
     * Apply reverse (invert) decoration to the text.
     *
     * @return self
     */
    public function reverse(): self
    {
        $this->decorateRules[] = Decorate::REVERSE;
        $this->decorateRules[] = Decorate::RESET_REVERSE;

        return $this;
    }

    /**
     * Apply hidden decoration to the text.
     *
     * @return self
     */
    public function hidden(): self
    {
        $this->decorateRules[] = Decorate::HIDDEN;
        $this->resetRules[]    = Decorate::RESET_HIDDEN;

        return $this;
    }

    /**
     * Add a raw terminal code or styling rule.
     *
     * @param RuleInterface|string $raw The raw terminal rule or escape sequence
     * @return self
     */
    public function raw(RuleInterface|string $raw): self
    {
        if ($raw instanceof ForegroundColor) {
            $this->textColorRule = $raw->get();

            return $this;
        }

        if ($raw instanceof BackgroundColor) {
            $this->bgColorRule = $raw->get();

            return $this;
        }

        $this->rawRules[] = [$raw];

        return $this;
    }

    /**
     * Set the reset rules directly.
     *
     * @param int[] $reset An array of reset codes
     * @return self
     */
    public function rawReset(array $reset): self
    {
        $this->resetRules = $reset;

        return $this;
    }

    /**
     * Set the text (foreground) color.
     *
     * @param ForegroundColor|string $color The color object or hex code string
     * @return self
     */
    public function textColor(ForegroundColor|string $color): self
    {
        $this->textColorRule = $color instanceof ForegroundColor
            ? $color->get()
            : Colors::hexText($color)->get()
        ;

        return $this;
    }

    /**
     * Set the background color.
     *
     * @param BackgroundColor|string $color The color object or hex code string
     * @return self
     */
    public function bgColor(BackgroundColor|string $color): self
    {
        $this->bgColorRule = $color instanceof BackgroundColor
            ? $color->get()
            : Colors::hexBg($color)->get();

        return $this;
    }

    /**
     * Append repeated content to the current text.
     *
     * @param string $content The content to repeat
     * @param int    $repeat  Number of repetitions
     * @return self
     */
    public function repeat(string $content, int $repeat = 1): self
    {
        /** @noinspection PhpConditionCanBeReplacedWithMinMaxCallInspection */
        $repeat = $repeat < 0 ? 0 : $repeat;

        return $this->push(
            str_repeat($content, $repeat)
        );
    }

    /**
     * Append new line characters to the current text.
     *
     * @param int $repeat Number of new lines to append
     * @return self
     */
    public function newLines(int $repeat = 1): self
    {
        return $this->repeat("\n", $repeat);
    }

    /**
     * Append tab characters to the current text.
     *
     * @param int $count Number of tab characters to append
     * @return self
     */
    public function tabs(int $count = 1): self
    {
        return $this->repeat("\t", $count);
    }
}
