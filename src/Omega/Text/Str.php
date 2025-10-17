<?php

declare(strict_types=1);

namespace Omega\Text;

use Omega\Macroable\MacroableTrait;
use Omega\Text\Exceptions\NoReturnException;

use function array_keys;
use function array_map;
use function array_pop;
use function array_values;
use function explode;
use function iconv;
use function implode;
use function is_string;
use function mb_strpos;
use function mb_strtolower;
use function mb_strtoupper;
use function mb_substr;
use function preg_match;
use function preg_replace;
use function preg_split;
use function str_pad;
use function str_repeat;
use function str_replace;
use function strlen;
use function strncmp;
use function substr_compare;
use function trim;
use function ucfirst;
use function ucwords;

use const PHP_INT_MAX;
use const PREG_SPLIT_NO_EMPTY;
use const STR_PAD_LEFT;

final class Str
{
    use MacroableTrait;

    /**
     * Create new instance.
     *
     * @param string $text Input text
     * @return Text
     */
    public static function of(string $text): Text
    {
        return new Text($text);
    }

    /**
     * Return the character at the specified position.
     *
     * @param string $text  String text
     * @param int    $index character position
     * @return string|false
     */
    public static function charAt(string $text, int $index): string|false
    {
        return mb_substr($text, $index, 1);
    }

    /**
     * Join two or more string into once.
     *
     * @param array<int, string> $text          String array
     * @param string             $separator     Separator
     * @param string             $lastSeparator Separator before last item
     * @return string
     */
    public static function concat(array $text, string $separator = ' ', string $lastSeparator = ''): string
    {
        if ('' !== $lastSeparator) {
            $removeLast = array_pop($text);
            $text[]     = $lastSeparator;
            $text[]     = $removeLast;
        }

        return implode($separator, $text);
    }

    /**
     * Index of first occurrence of specified text with in string.
     *
     * @param string $text String to search
     * @param string $find Find
     * @return int|false
     */
    public static function indexOf(string $text, string $find): int|false
    {
        return mb_strpos($text, $find, 1);
    }

    /**
     * Last index of first occurrence of specified text with in string.
     *
     * @param string $text String to search
     * @param string $find Find
     * @return int|false
     */
    public static function lastIndexOf(string $text, string $find): int|false
    {
        return mb_strpos($text, $find, -1);
    }

    /**
     * Retrieves the matches of string against a search pattern.
     *
     * @param string $text    String
     * @param string $pattern String regular expression.
     * @return array<int, string>|null Null if not match found
     */
    public static function match(string $text, string $pattern): ?array
    {
        $matches    = [];
        $hasResult = preg_match($pattern, $text, $matches);

        if (1 === $hasResult) {
            return $matches;
        }

        return null;
    }

    /**
     * Find and replace specified text in string.
     *
     * @param string                    $original The subject text
     * @param string|array<int, string> $find     find
     * @param string|array<int, string> $replace  replace
     * @return string
     */
    public static function replace(string $original, string|array $find, string|array $replace): string
    {
        return str_replace($find, $replace, $original);
    }

    /**
     * Search for matching text and return as position.
     *
     * @param string $text String to search
     * @param string $find Find
     * @return int|false
     */
    public static function search(string $text, string $find): int|false
    {
        return mb_strpos($text, $find);
    }

    /**
     * Extracts a section of string.
     *
     * @param string   $text   String to slice
     * @param int      $start  Start position text
     * @param int|null $length Length of string
     * @return string|false
     */
    public static function slice(string $text, int $start, ?int $length): string|false
    {
        $textLength = $length ?? self::length($text);

        return mb_substr($text, $start, $textLength);
    }

    /**
     * Splits a string into array of string.
     *
     * @param string $text     string to split
     * @param string $separator Separator
     * @param int    $limit    Limit array length
     * @return string[]|false
     */
    public static function split(string $text, string $separator = '', int $limit = PHP_INT_MAX): array|false
    {
        return '' === $separator
            ? preg_split('//', $text, -1, PREG_SPLIT_NO_EMPTY)
            : explode($separator, $text, $limit);
    }

    /**
     * Convert string to lowercase.
     *
     * @param string $text Input string
     * @return string
     */
    public static function toLowerCase(string $text): string
    {
        return mb_strtolower($text);
    }

    /**
     * Convert string to lowercase.
     *
     * @param string $text Input string
     * @return string
     */
    public static function toUpperCase(string $text): string
    {
        return mb_strtoupper($text);
    }

    /**
     * Get string after find text find.
     */
    public static function after(string $text, string $find): string
    {
        $length = strlen($find);
        if (false === ($pos = Str::indexOf($text, $find))) {
            return $text;
        }

        return mb_substr($text, $pos + $length);
    }

    /**
     * Make first character uppercase.
     *
     * @param string $text Input string
     * @return string
     */
    public static function firstUpper(string $text): string
    {
        return ucfirst($text);
    }

    /**
     * Make first character uppercase each words.
     *
     * @param string $text Input string
     * @return string
     */
    public static function firstUpperAll(string $text): string
    {
        return ucwords($text);
    }

    /**
     * Make text separate with dash (snake_case).
     *
     * @param string $text input text
     * @return string
     */
    public static function toSnakeCase(string $text): string
    {
        return str_replace([' ', '-', '_', '+'], '_', $text);
    }

    /**
     * Make text separated with - (kebab-case).
     *
     * @param string $text input text
     * @return string
     */
    public static function toKebabCase(string $text): string
    {
        return str_replace([' ', '-', '_', '+'], '-', $text);
    }

    /**
     * Make text each word start with capital (pascalcase).
     *
     * @param string $text input text
     * @return string
     */
    public static function toPascalCase(string $text): string
    {
        $spaceCase  = str_replace(['-', '_', '+'], ' ', $text);
        $firstUpper = Str::firstUpperAll($spaceCase);

        return str_replace(' ', '', $firstUpper);
    }

    /**
     * Make text camelcase.
     *
     * @param string $text input text
     * @return string
     */
    public static function toCamelCase(string $text): string
    {
        $spaceCase  = str_replace(['-', '_', '+'], ' ', $text);
        $arrText    = explode(' ', $spaceCase);
        $result     = [];
        $firstText  = true;

        foreach ($arrText as $text) {
            if ($firstText) {
                $result[]   = mb_strtolower($text);
                $firstText = false;
                continue;
            }

            $result[] = ucfirst($text);
        }

        return implode('', $result);
    }

    /**
     * Make slugify (url-title).
     *
     * @param string $text input text
     * @return string
     *
     * @throw NoReturnException
     */
    public static function slug(string $text): string
    {
        $original = $text;

        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = mb_strtolower($text);

        if (empty($text)) {
            throw new NoReturnException(__FUNCTION__, $original);
        }

        return $text;
    }

    /**
     * Make multiple text (repeat).
     *
     * @param string $text     Text
     * @param int    $multiple Number repeat (less than 0 will return empty)
     * @return string
     */
    public static function repeat(string $text, int $multiple): string
    {
        return str_repeat($text, $multiple);
    }

    /**
     * Get string length (0 if empty).
     *
     * @param string $text
     * @return int
     */
    public static function length(string $text): int
    {
        return strlen($text);
    }

    /**
     * Render template text.
     *
     * @param string                $template       Template string
     * @param array<string, string> $data           Template data to replace placeholders
     * @param string                $openDelimiter  Opening delimiter (recommended: '{')
     * @param string                $closeDelimiter Closing delimiter (recommended: '}')
     * @return string Rendered template with replaced data
     */
    public static function template(
        string $template,
        array $data,
        string $openDelimiter = '{',
        string $closeDelimiter = '}'
    ): string {
        if ('{' === $openDelimiter && '}' === $closeDelimiter) {
            $template = preg_replace(['/\\{\s+/', '/\s+\\}/'], ['{', '}'], $template);
        }

        $keys = [];
        foreach ($data as $key => $value) {
            $keys[] = $openDelimiter . $key . $closeDelimiter;
        }

        return str_replace($keys, $data, $template);
    }

    /**
     * Fill string (start) with string if length is less.
     *
     * @param string $text      String Text
     * @param string $fill      String fill for miss length
     * @param int    $maxLength max length of output string
     * @return string
     */
    public static function fill(string $text, string $fill, int $maxLength): string
    {
        return str_pad($text, $maxLength, $fill, STR_PAD_LEFT);
    }

    /**
     * Fill string (end) with string if length is less.
     *
     * @param string $text      String text
     * @param string $fill      String fill for miss length
     * @param int    $maxLength max length of output string
     * @return string
     */
    public static function fillEnd(string $text, string $fill, int $maxLength): string
    {
        // Uses the native str_pad function's default value STR_PAD_RIGHT
        return str_pad($text, $maxLength, $fill);
    }

    /**
     * Create mask string.
     *
     * @param string $text       String text
     * @param string $mask       Mask
     * @param int    $start      Start position mask
     * @param int    $maskLength Mask length
     * @return string String with mask
     */
    public static function mask(string $text, string $mask, int $start, int $maskLength = 9999): string
    {
        // negative position, count from end text
        if ($start < 0) {
            $start = strlen($text) + $start;
        }

        $end = $start + $maskLength;
        $start--;
        $arrText = preg_split('//', $text, -1, PREG_SPLIT_NO_EMPTY);
        $newText = array_map(function ($index, $string) use ($mask, $start, $end) {
            if ($index > $start && $index < $end) {
                return $mask;
            }

            return $string;
        }, array_keys($arrText), array_values($arrText));

        return implode('', $newText);
    }

    /**
     * Check determinate input is string.
     *
     * @param string $text Text
     * @return bool
     */
    public static function isString(string $text): bool
    {
        return is_string($text);
    }

    /**
     * Check string is empty string.
     *
     * @param string $text
     * @return bool
     */
    public static function isEmpty(string $text): bool
    {
        return '' === $text;
    }

    /**
     * Retrieves the matches of string against a search pattern.
     *
     * @param string $text    String
     * @param string $pattern String regular expression.
     * @return bool
     */
    public static function isMatch(string $text, string $pattern): bool
    {
        $hasResult = preg_match($pattern, $text);

        if (1 === $hasResult) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves the matches of string against a search pattern.
     * Shorthand for `isMatch`.
     *
     * @param string $text    String
     * @param string $pattern String regular expression.
     * @return bool
     */
    public static function is(string $text, string $pattern): bool
    {
        return Str::isMatch($text, $pattern);
    }

    // Backward Compatible php 8.0 --------------------------------

    /**
     * Check text contain with.
     *
     * @param string $text Text
     * @param string $find Text contain
     * @return bool True if text contain
     */
    public static function contains(string $text, string $find): bool
    {
        return '' === $find || false !== mb_strpos($text, $find);
    }

    /**
     * Check text starts with.
     *
     * @param string $text      Text
     * @param string $startWith Start with
     * @return bool True if text starts with
     */
    public static function startsWith(string $text, string $startWith): bool
    {
        return 0 === strncmp($text, $startWith, strlen($startWith));
    }

    /**
     * Check text ends with.
     *
     * @param string $text       Text
     * @param string $startWith Start with
     * @return bool True if text ends with
     */
    public static function endsWith(string $text, string $startWith): bool
    {
        if ('' === $startWith || $startWith === $text) {
            return true;
        }

        if ('' === $text) {
            return false;
        }

        $needleLength = strlen($startWith);

        return $needleLength <= strlen($text) && 0 === substr_compare($text, $startWith, -$needleLength);
    }

    /**
     * Truncate text to limited length.
     *
     * @param string $text              Text
     * @param int    $length            Maximum text length
     * @param string $truncateCharacter Truncate character
     * @return string
     */
    public static function limit(string $text, int $length, string $truncateCharacter = '...'): string
    {
        return self::slice($text, 0, $length) . $truncateCharacter;
    }
}
