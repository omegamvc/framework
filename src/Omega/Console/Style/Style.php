<?php

declare(strict_types=1);

namespace Omega\Console\Style;

use Omega\Console\IO\OutputStreamInterface;
use Omega\Console\Style\Color\RuleInterface;
use Omega\Console\Style\Color\BackgroundColor;
use Omega\Console\Style\Color\ForegroundColor;
use Omega\Console\Traits\CommandTrait;
use Omega\Text\Str;

use function max;
use function method_exists;
use function Omega\Text\text;
use function preg_replace;
use function str_repeat;
use function strlen;
use function strtolower;

use const PHP_EOL;

/**
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
 */
class Style
{
    use CommandTrait;

    /** @var array<int, int> Array of command rule. */
    private array $rules = [];

    /** @var array<int, array<int, string|int>> Array of command rule. */
    private array $rawRules = [];

    /** @var array<int, int> Array of command rule. */
    private array $resetRules = [Decorate::RESET];

    /** @var array<int, int> Rule of text color. */
    private array $textColorRule = [Decorate::TEXT_DEFAULT];

    /** @var array<int, int> Rule of background color. */
    private array $bgColorRule = [Decorate::BG_DEFAULT];

    /** @var array<int, int> Rule of text decorate. */
    private array $decorateRules = [];

    /**  @var int|string String to style. */
    private string|int $text;

    /** @var int Length of text. */
    private int $length = 0;

    /** @var string Reference from preview text (like prefix). */
    private string $ref = '';

    /** @var OutputStreamInterface|null */
    private ?OutputStreamInterface $outputStream = null;

    /**
     * @param int|string $text set text to decorate
     */
    public function __construct(int|string $text = '')
    {
        $this->text   = $text;
        $this->length = strlen((string) $text);
    }

    /**
     * Invoke new Rule class.
     *
     * @param string|int $text set text to decorate
     * @return self
     */
    public function __invoke(string|int $text): self
    {
        $this->text   = $text;
        $this->length = strlen((string) $text);

        return $this->flush();
    }

    /**
     * Get string of terminal formatted style.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString($this->text, $this->ref);
    }

    /**
     * Call exist method from trait.
     *
     * @param string            $name
     * @param array<int, mixed> $arguments
     * @return self
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
     * Render text, reference with current rule.
     *
     * @param string $text Text tobe render with rule (this)
     * @param string $ref  Text reference to be added begin text
     * @return string
     */
    public function toString(string|int $text, string $ref = ''): string
    {
        // make sure not push empty text
        if ($text == '' && $ref == '') {
            return '';
        }

        // flush
        $this->rules = [];

        // font color
        foreach ($this->textColorRule as $text_color) {
            $this->rules[] = $text_color;
        }

        // bg color
        foreach ($this->bgColorRule as $bg_color) {
            $this->rules[] = $bg_color;
        }

        // decorate
        foreach ($this->decorateRules as $decorate) {
            $this->rules[] = $decorate;
        }

        // raw
        foreach ($this->rawRules as $raws) {
            foreach ($raws as $raw) {
                $this->rules[] = $raw;
            }
        }

        return $ref . $this->rules($this->rules, $text, true, $this->resetRules);
    }

    /**
     * Flush class.
     *
     * @return self
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
     * Set reference (add before main text).
     *
     * @param string $textReference
     * @return self
     */
    private function ref(string $textReference): self
    {
        $this->ref = $textReference;

        return $this;
    }

    /**
     * Chain code (continue with other text).
     *
     * @param string $text text
     * @return self
     */
    public function push(string|int $text): self
    {
        $ref        = $this->toString($this->text, $this->ref);
        $this->text = $text;
        $this->length += strlen((string)$text);

        return $this->flush()->ref($ref);
    }

    /**
     * Push Style.
     *
     * @param Style $style Style to push
     * @return self
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
     * Get text length without rule counted.
     *
     * @return int
     */
    public function length(): int
    {
        return $this->length;
    }

    /**
     * Print terminal style.
     *
     * @param bool $newLine True if print with new line in end line
     * @return void
     */
    public function out(bool $newLine = true): void
    {
        $out = $this . ($newLine ? PHP_EOL : null);

        echo $out;
    }

    /**
     * Print terminal style if condition true.
     *
     * @param bool $condition If true will echo out
     * @param bool $newLine  True if print with new line in end line
     * @return void
     */
    public function outIf(bool $condition, bool $newLine = true): void
    {
        if ($condition) {
            $out = $this . ($newLine ? PHP_EOL : null);

            echo $out;
        }
    }

    /**
     * Print to terminal and continue.
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
     * Write stream out.
     *
     * @param bool $newLine True if print with new line in end line
     * @return void
     */
    public function write(bool $newLine = true): void
    {
        $out = $this . ($newLine ? PHP_EOL : null);

        $this->outputStream?->write($out);
    }

    /**
     * Clear current line (original text is keep).
     *
     * @param int $line
     * @return void
     */
    public function clear(int $line = -1): void
    {
        $this->clearLine($line);
    }

    /**
     * Replace current line (original text is keep).
     *
     * @param string $text
     * @param int $line
     * @return void
     */
    public function replace(string $text, int $line = -1): void
    {
        $this->replaceLine($text, $line);
    }

    /**
     * @param OutputStreamInterface $resourceOutputStream
     * @return $this
     */
    public function setOutputStream(OutputStreamInterface $resourceOutputStream): self
    {
        $this->outputStream = $resourceOutputStream;

        return $this;
    }

    /**
     * Reset all attributes (set reset decorate to be 0).
     *
     * @return self
     */
    public function resetDecorate(): self
    {
        $this->resetRules = [Decorate::RESET];

        return $this;
    }

    /**
     * Text decorate bold.
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
     * Text decorate underline.
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
     * Text decorate blink.
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
     * Text decorate reverse/invert.
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
     * Text decorate hidden.
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
     * Add raw terminal code.
     *
     * @param string|RuleInterface $raw Raw terminal code
     * @return self
     */
    public function raw(string|RuleInterface $raw): self
    {
        if ($raw instanceof ForegroundColor) {
            $this->textColorRule = $raw->getRule();

            return $this;
        }

        if ($raw instanceof BackgroundColor) {
            $this->bgColorRule = $raw->getRule();

            return $this;
        }

        $this->rawRules[] = [$raw];

        return $this;
    }

    /**
     * @param int[] $reset rules reset
     * @return self
     */
    public function rawReset(array $reset): self
    {
        $this->resetRules = $reset;

        return $this;
    }

    /**
     * Set text color.
     *
     * @param ForegroundColor|string $color
     * @return self
     */
    public function textColor(ForegroundColor|string $color): self
    {
        $this->textColorRule = $color instanceof ForegroundColor
            ? $color->getRule()
            : Colors::hexText($color)->getRule()
        ;

        return $this;
    }

    /**
     * Set Background color.
     *
     * @param BackgroundColor|string $color
     * @return self
     */
    public function bgColor(BackgroundColor|string $color): self
    {
        $this->bgColorRule = $color instanceof BackgroundColor
            ? $color->getRule()
            : Colors::hexBg($color)->getRule();

        return $this;
    }

    /**
     * Push/insert repeat character.
     *
     * @param string $content
     * @param int $repeat
     * @return self
     */
    public function repeat(string $content, int $repeat = 1): self
    {
        $repeat = max($repeat, 0);

        return $this->push(
            str_repeat($content, $repeat)
        );
    }

    /**
     * Push/insert new lines.
     *
     * @param int $repeat
     * @return self
     */
    public function newLines(int $repeat = 1): self
    {
        return $this->repeat("\n", $repeat);
    }

    /**
     * Push/insert tabs.
     *
     * @param int $count
     * @return self
     */
    public function tabs(int $count = 1): self
    {
        return $this->repeat("\t", $count);
    }
}
