<?php

declare(strict_types=1);

namespace Omega\Text;

use Omega\Text\Exceptions\NoReturnException;

use function gettype;

class Text
{
    /**
     * Original string input.
     *
     * @var string
     */
    private string $original;

    /**
     * Current string.
     *
     * @var string
     */
    private string $current;

    /**
     * Log string modifier.
     *
     * @var array<string, array<string, string>>
     */
    private array $latest;

    /**
     * Throw when string method return 'false' instance 'string'.
     *
     * @var bool
     */
    private bool $throwOnFailure = false;

    /**
     * Create string class.
     *
     * @param string $text Input string
     */
    public function __construct(string $text)
    {
        $this->original = $text;
        $this->execute($text, __FUNCTION__);
    }

    /**
     * Basically is history for text modify.
     *
     * @param bool|string|array<int|string, string> $text          new incoming text
     * @param string                                $functionName Method to call (Str::class)
     * @return string
     */
    private function execute(array|bool|string $text, string $functionName): string
    {
        if (Str::isString($text)) {
            $this->current = $text;
        }

        $this->latest[] = [
            'function'  => $functionName,
            'return'    => $text,
            'type'      => gettype($text),
        ];

        return $text;
    }

    /**
     * Push new string text without erase history.
     *
     * @param string $text New text
     * @return self
     */
    public function text(string $text): self
    {
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Get last/current string text.
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->current;
    }

    /**
     * Get last/current string text.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getText();
    }

    /**
     * Get string history.
     *
     * @return array<string, array<string, string>>
     */
    public function logs(): array
    {
        return $this->latest;
    }

    /**
     * Reset or flush this class to origin string.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->current          = $this->original;
        $this->latest           = [];
        $this->throwOnFailure = false;

        return $this;
    }

    /**
     * Refresh class with new text.
     *
     * @param string $text Input string
     * @return self
     */
    public function refresh(string $text): self
    {
        $this->original = $text;

        return $this->reset();
    }

    /**
     * Throw when string method return 'false' instance 'string'.
     *
     * @param bool $throwError Throw on failure
     * @return self
     */
    public function throwOnFailure(bool $throwError): self
    {
        $this->throwOnFailure = $throwError;

        return $this;
    }

    /**
     * Return the character at the specified position.
     *
     * @param int $index character position
     * @return self
     */
    public function charAt(int $index): self
    {
        $text = Str::charAt($this->current, $index);

        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Extracts a section of string.
     *
     * @param int      $start  Start position text
     * @param int|null $length Length of string
     * @return self
     */
    public function slice(int $start, ?int $length = null): self
    {
        $text = Str::slice($this->current, $start, $length);

        if ($this->throwOnFailure && false === $text) {
            throw new NoReturnException(__FUNCTION__, $this->current);
        }

        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Convert string to lowercase.
     *
     * @return self
     */
    public function lower(): self
    {
        $text = Str::toLowerCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Convert string to lowercase.
     *
     * @return self
     */
    public function upper(): self
    {
        $text = Str::toUpperCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make first character uppercase.
     *
     * @return self
     */
    public function firstUpper(): self
    {
        $text = Str::firstUpper($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make first character uppercase each words.
     *
     * @return self
     */
    public function firstUpperAll(): self
    {
        $text = Str::firstUpperAll($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make text separate with underscore (snake_case).
     *
     * @return self
     */
    public function snake(): self
    {
        $text = Str::toSnakeCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make text separate with - (kebab case).
     *
     * @return self
     */
    public function kebab(): self
    {
        $text = Str::toKebabCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make text each word start with capital (pascalcase).
     *
     * @return self
     */
    public function pascal(): self
    {
        $text = Str::toPascalCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make text camelcase.
     *
     * @return self
     */
    public function camel(): self
    {
        $text = Str::toCamelCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make text each word start with capital (pascalcase).
     *
     * @return self
     */
    public function slug(): self
    {
        $text = Str::slug($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Fill string (start) with string if length is less.
     *
     * @param string $fill   String fill for miss length
     * @param int    $length Max length of output string
     * @return self
     */
    public function fill(string $fill, int $length): self
    {
        $text = Str::fill($this->current, $fill, $length);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Fill string (end) with string if length is less.
     *
     * @param string $fill   String fill for miss length
     * @param int    $length Max length of output string
     * @return self
     */
    public function fillEnd(string $fill, int $length): self
    {
        $text = Str::fillEnd($this->current, $fill, $length);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Create mask string.
     *
     * @param string $mask       Mask
     * @param int    $start      Start position mask
     * @param int    $maskLength Mask length
     * @return self
     */
    public function mask(string $mask, int $start, int $maskLength = 9999): self
    {
        $text = Str::mask($this->current, $mask, $start, $maskLength);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Truncate text to limited length.
     *
     * @param int    $length            Length text
     * @param string $truncateCharacter Truncate character
     * @return self
     */
    public function limit(int $length, string $truncateCharacter = '...'): self
    {
        $text = Str::limit($this->current, $length, $truncateCharacter);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Get text after text found.
     */
    public function after(string $find): self
    {
        $this->execute(
            Str::after($this->current, $find),
            __FUNCTION__
        );

        return $this;
    }

    /**
     * Get string length (0 if empty).
     *
     * @return int
     */
    public function length(): int
    {
        return Str::length($this->current);
    }

    /**
     * Index of first occurrence of specified text with in string.
     *
     * @param string $find Find
     * @return int|false
     */
    public function indexOf(string $find): int|false
    {
        return Str::indexOf($this->current, $find);
    }

    /**
     * Last index of first occurrence of specified text with in string.
     *
     * @param string $find Find
     * @return int|false
     */
    public function lastIndexOf(string $find): int|false
    {
        return Str::lastIndexOf($this->current, $find);
    }

    /**
     * Check string is empty string.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return Str::isEmpty($this->current);
    }

    /**
     * Check string is empty string.
     *
     * @param string $pattern String regular expression
     * @return bool
     */
    public function is(string $pattern): bool
    {
        return Str::isMatch($this->current, $pattern);
    }

    /**
     * Check string is empty string.
     *
     * @param string $pattern String regular expression
     * @return bool
     */
    public function isMatch(string $pattern): bool
    {
        return $this->is($pattern);
    }

    /**
     * Check text contain with.
     *
     * @param string $find Text contain
     * @return bool True if text contain
     */
    public function contains(string $find): bool
    {
        return Str::contains($this->current, $find);
    }

    /**
     * Check text starts with.
     *
     * @param string $startWith Start with
     * @return bool True if text starts with
     */
    public function startsWith(string $startWith): bool
    {
        return Str::startsWith($this->current, $startWith);
    }

    /**
     * Check text ends with.
     *
     * @param string $endWith Start with
     * @return bool True if text ends with
     */
    public function endsWith(string $endWith): bool
    {
        return Str::endsWith($this->current, $endWith);
    }
}
