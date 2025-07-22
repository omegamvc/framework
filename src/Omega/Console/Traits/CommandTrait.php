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

use Omega\Console\Style\Color\BackgroundColor;
use Omega\Console\Style\Color\ForegroundColor;
use Omega\Console\Style\Decorate;

/**
 * Trait CommandTrait
 *
 * Provides methods for styling console output text using ANSI escape codes.
 *
 * Includes methods to apply foreground and background colors, text decorations
 * such as bold, underline, blink, reverse, and hidden, with predefined color codes
 * as well as just-in-time (JIT) color support using ForegroundColor and BackgroundColor objects.
 *
 * This trait depends on the PrinterTrait for the `rule` and `rules` methods that
 * actually apply the ANSI codes.
 *
 * @category   Omega
 * @package    Console
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
trait CommandTrait
{
    use PrinterTrait;

    /**
     * Apply red text color (bash code 31).
     *
     * @param string $text Text to style.
     * @return string Styled text with red foreground.
     */
    protected function textRed(string $text): string
    {
        return $this->rule(Decorate::TEXT_RED, $text);
    }


    /**
     * Apply yellow text color (bash code 33).
     *
     * @param string $text Text to style.
     * @return string Styled text with yellow foreground.
     */
    protected function textYellow(string $text): string
    {
        return $this->rule(Decorate::TEXT_YELLOW, $text);
    }

    /**
     * Apply green text color (bash code 32).
     *
     * @param string $text Text to style.
     * @return string Styled text with green foreground.
     */
    protected function textGreen(string $text): string
    {
        return $this->rule(Decorate::TEXT_GREEN, $text);
    }

    /**
     * Apply blue text color (bash code 34).
     *
     * @param string $text Text to style.
     * @return string Styled text with blue foreground.
     */
    protected function textBlue(string $text): string
    {
        return $this->rule(Decorate::TEXT_BLUE, $text);
    }

    /**
     * Apply dim text style (bash code 2).
     *
     * @param string $text Text to style.
     * @return string Styled text with dim effect.
     */
    protected function textDim(string $text): string
    {
        return $this->rule(Decorate::TEXT_DIM, $text);
    }

    /**
     * Apply magenta text color (bash code 35).
     *
     * @param string $text Text to style.
     * @return string Styled text with magenta foreground.
     */
    protected function textMagenta(string $text): string
    {
        return $this->rule(Decorate::TEXT_MAGENTA, $text);
    }

    /**
     * Apply cyan text color (bash code 36).
     *
     * @param string $text Text to style.
     * @return string Styled text with cyan foreground.
     */
    protected function textCyan(string $text): string
    {
        return $this->rule(Decorate::TEXT_CYAN, $text);
    }

    /**
     * Apply light gray text color (bash code 37).
     *
     * @param string $text Text to style.
     * @return string Styled text with light gray foreground.
     */
    protected function textLightGray(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_GRAY, $text);
    }

    /**
     * Apply dark gray text color (bash code 90).
     *
     * @param string $text Text to style.
     * @return string Styled text with dark gray foreground.
     */
    protected function textDarkGray(string $text): string
    {
        return $this->rule(Decorate::TEXT_DARK_GRAY, $text);
    }

    /**
     * Apply light red text color (bash code 91).
     *
     * @param string $text Text to style.
     * @return string Styled text with light red foreground.
     */
    protected function textLightRed(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_RED, $text);
    }

    /**
     * Apply light green text color (bash code 92).
     *
     * @param string $text Text to style.
     * @return string Styled text with light green foreground.
     */
    protected function textLightGreen(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_GREEN, $text);
    }

    /**
     * Apply light yellow text color (bash code 93).
     *
     * @param string $text Text to style.
     * @return string Styled text with light yellow foreground.
     */
    protected function textLightYellow(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_YELLOW, $text);
    }

    /**
     * Apply light blue text color (bash code 94).
     *
     * @param string $text Text to style.
     * @return string Styled text with light blue foreground.
     */
    protected function textLightBlue(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_BLUE, $text);
    }

    /**
     * Apply light magenta text color (bash code 95).
     *
     * @param string $text Text to style.
     * @return string Styled text with light magenta foreground.
     */
    protected function textLightMagenta(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_MAGENTA, $text);
    }

    /**
     * Apply light cyan text color (bash code 96).
     *
     * @param string $text Text to style.
     * @return string Styled text with light cyan foreground.
     */
    protected function textLightCyan(string $text): string
    {
        return $this->rule(Decorate::TEXT_LIGHT_CYAN, $text);
    }

    /**
     * Apply white text color (bash code 37).
     *
     * @param string $text Text to style.
     * @return string Styled text with white foreground.
     */
    protected function textWhite(string $text): string
    {
        return $this->rule(Decorate::TEXT_WHITE, $text);
    }

    /**
     * Apply red background color (bash code 41).
     *
     * @param string $text Text to style.
     * @return string Styled text with red background.
     */
    protected function bgRed(string $text): string
    {
        return $this->rule(Decorate::BG_RED, $text);
    }

    /**
     * Apply yellow background color (bash code 43).
     *
     * @param string $text Text to style.
     * @return string Styled text with yellow background.
     */
    protected function bgYellow(string $text): string
    {
        return $this->rule(Decorate::BG_YELLOW, $text);
    }

    /**
     * Apply yellow background color (bash code 43).
     *
     * @param string $text Text to style.
     * @return string Styled text with yellow background.
     */
    protected function bgGreen(string $text): string
    {
        return $this->rule(Decorate::BG_GREEN, $text);
    }

    /**
     * Apply blue background color (bash code 44).
     *
     * @param string $text Text to style.
     * @return string Styled text with blue background.
     */
    protected function bgBlue(string $text): string
    {
        return $this->rule(Decorate::BG_BLUE, $text);
    }

    /**
     * Apply magenta background color (bash code 45).
     *
     * @param string $text Text to style.
     * @return string Styled text with magenta background.
     */
    protected function bgMagenta(string $text): string
    {
        return $this->rule(Decorate::BG_MAGENTA, $text);
    }

    /**
     * Apply cyan background color (bash code 46).
     *
     * @param string $text Text to style.
     * @return string Styled text with cyan background.
     */
    protected function bgCyan(string $text): string
    {
        return $this->rule(Decorate::BG_CYAN, $text);
    }

    /**
     * Apply light gray background color (bash code 47).
     *
     * @param string $text Text to style.
     * @return string Styled text with light gray background.
     */
    protected function bgLightGray(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_GRAY, $text);
    }

    /**
     * Apply dark gray background color (bash code 100).
     *
     * @param string $text Text to style.
     * @return string Styled text with dark gray background.
     */
    protected function bgDarkGray(string $text): string
    {
        return $this->rule(Decorate::BG_DARK_GRAY, $text);
    }

    /**
     * Apply light red background color (bash code 101).
     *
     * @param string $text Text to style.
     * @return string Styled text with light red background.
     */
    protected function bgLightRed(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_RED, $text);
    }

    /**
     * Apply light green background color (bash code 102).
     *
     * @param string $text Text to style.
     * @return string Styled text with light green background.
     */
    protected function bgLightGreen(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_GREEN, $text);
    }

    /**
     * Apply light yellow background color (bash code 103).
     *
     * @param string $text Text to style.
     * @return string Styled text with light yellow background.
     */
    protected function bgLightYellow(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_YELLOW, $text);
    }

    /**
     * Apply light blue background color (bash code 104).
     *
     * @param string $text Text to style.
     * @return string Styled text with light blue background.
     */
    protected function bgLightBlue(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_BLUE, $text);
    }

    /**
     * Apply light magenta background color (bash code 105).
     *
     * @param string $text Text to style.
     * @return string Styled text with light magenta background.
     */
    protected function bgLightMagenta(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_MAGENTA, $text);
    }

    /**
     * Apply light cyan background color (bash code 106).
     *
     * @param string $text Text to style.
     * @return string Styled text with light cyan background.
     */
    protected function bgLightCyan(string $text): string
    {
        return $this->rule(Decorate::BG_LIGHT_CYAN, $text);
    }

    /**
     * Apply white background color (bash code 107).
     *
     * @param string $text Text to style.
     * @return string Styled text with white background.
     */
    protected function bgWhite(string $text): string
    {
        return $this->rule(Decorate::BG_WHITE, $text);
    }

    /**
     * Apply a custom 256-color foreground to the given text.
     *
     * @param ForegroundColor $color The foreground color instance (0–255).
     * @param string $text The text to style.
     * @return string Styled text with the specified foreground color.
     */
    protected function textColor(ForegroundColor $color, string $text): string
    {
        return $this->rules($color->get(), $text);
    }

    /**
     * Apply a custom 256-color background to the given text.
     *
     * @param BackgroundColor $color The background color instance (0–255).
     * @param string $text The text to style.
     * @return string Styled text with the specified background color.
     */
    protected function bgColor(BackgroundColor $color, string $text): string
    {
        return $this->rules($color->get(), $text);
    }

    /**
     * Apply bold formatting to the given text.
     *
     * @param string $text The text to style.
     * @return string Bold-formatted text.
     */
    protected function textBold(string $text): string
    {
        return $this->rule(Decorate::BOLD, $text, true, Decorate::RESET_BOLD);
    }

    /**
     * Apply underline formatting to the given text.
     *
     * @param string $text The text to style.
     * @return string Underlined text.
     */
    protected function textUnderline(string $text): string
    {
        return $this->rule(Decorate::UNDERLINE, $text, true, Decorate::RESET_UNDERLINE);
    }

    /**
     * Apply blinking effect to the given text.
     *
     * @param string $text The text to style.
     * @return string Blinking text.
     */
    protected function textBlink(string $text): string
    {
        return $this->rule(Decorate::BLINK, $text, true, Decorate::RESET_BLINK);
    }

    /**
     * Invert foreground and background colors of the given text.
     *
     * @param string $text The text to style.
     * @return string Text with reversed foreground and background colors.
     */
    protected function textReverse(string $text): string
    {
        return $this->rule(Decorate::REVERSE, $text, true, Decorate::RESET_REVERSE);
    }

    /**
     * Hide the given text (e.g., for passwords or invisible output).
     *
     * @param string $text The text to style.
     * @return string Hidden (non-visible) text.
     */
    protected function textHidden(string $text): string
    {
        return $this->rule(Decorate::HIDDEN, $text, true, Decorate::RESET_HIDDEN);
    }
}
