<?php

/**
 * Part of Omega MVC - Utils Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Utils;

use function function_exists;
use function is_null;
use function mb_strtolower;
use function mb_strlen;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function str_contains;
use function str_replace;
use function strlen;
use function substr;
use function trim;

/**
 * Str class.
 *
 * The 'Str' class provides a collection of static methods for string manipulation
 * and handling. This utility class offers various functions for common string
 * operations such as concatenation, substring extraction, trimming, case conversion,
 * and pattern matching. By encapsulating these functionalities within a single class,
 * 'Str' simplifies string processing tasks and promotes code reusability. Developers
 * can utilize the methods provided by 'Str' to efficiently work with strings in their
 * applications, enhancing readability, maintainability, and overall code quality.
 *
 * @category  Omega
 * @package   Utils
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
class Str
{
    /**
     * Transliterate a UTF-8 value to ascii.
     *
     * @param string $value    Holds the value to transliterate.
     * @param string $language Holds the language to transliterate.
     * @return string Return the transliterated ascii value.
     */
    public static function ascii(string $value, string $language = 'en'): string
    {
        $languageSpecificCharsArray = static::languageSpecificCharsArray($language);

        if (!is_null($languageSpecificCharsArray)) {
            $value = str_replace($languageSpecificCharsArray[0], $languageSpecificCharsArray[1], $value);
        }

        foreach (static::charsArray() as $key => $val) {
            $value = str_replace($val, $key, $value);
        }

        return preg_replace('/[^\x20-\x7E]/u', '', $value);
    }

    /**
     * Returns the replacements for the ascii method.
     *
     * Note: Adapted from Stringy\Stringy.
     *
     * @return array Return the replacements for the ascii method.
     */
    protected static function charsArray(): array
    {
        static $charsArray;

        if (isset($charsArray)) {
            return $charsArray;
        }

        return $charsArray = [
            '0' => ['°', '₀', '۰', '０'],
            '1' => ['¹', '₁', '۱', '１'],
            '2' => ['²', '₂', '۲', '２'],
            '3' => ['³', '₃', '۳', '３'],
            '4' => ['⁴', '₄', '۴', '٤', '４'],
            '5' => ['⁵', '₅', '۵', '٥', '５'],
            '6' => ['⁶', '₆', '۶', '٦', '６'],
            '7' => ['⁷', '₇', '۷', '７'],
            '8' => ['⁸', '₈', '۸', '８'],
            '9' => ['⁹', '₉', '۹', '９'],
            'a' => ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â',
                'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å', 'α', 'ά', 'ἀ', 'ἁ',
                'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ',
                'ᾆ', 'ᾇ', 'ὰ', 'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а',
                'أ', 'အ', 'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا', 'ａ', 'ä', 'א'],
            'b' => ['б', 'β', 'ب', 'ဗ', 'ბ', 'ｂ', 'ב'],
            'c' => ['ç', 'ć', 'č', 'ĉ', 'ċ', 'ｃ'],
            'd' => ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ', 'ｄ', 'ד'],
            'e' => ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'ë',
                'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ', 'ἑ', 'ἒ', 'ἓ', 'ἔ',
                'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э', 'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე',
                'ए', 'إ', 'ئ', 'ｅ'],
            'f' => ['ф', 'φ', 'ف', 'ƒ', 'ფ', 'ｆ', 'פ', 'ף'],
            'g' => ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ', 'ｇ', 'ג'],
            'h' => ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ', 'ｈ', 'ה'],
            'i' => ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į', 'ı', 'ι', 'ί',
                'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ', 'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ',
                'ῑ', 'ῒ', 'ΐ', 'ῖ', 'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ',
                'ი', 'इ', 'ی', 'ｉ', 'י'],
            'j' => ['ĵ', 'ј', 'Ј', 'ჯ', 'ج', 'ｊ'],
            'k' => ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک', 'ｋ', 'ק'],
            'l' => ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ', 'ｌ', 'ל'],
            'm' => ['м', 'μ', 'م', 'မ', 'მ', 'ｍ', 'מ', 'ם'],
            'n' => ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ', 'ｎ', 'נ'],
            'o' => ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ',
                'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő', 'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ',
                'ὄ', 'ὅ', 'ὸ', 'ό', 'о', 'و', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ', 'ｏ', 'ö'],
            'p' => ['п', 'π', 'ပ', 'პ', 'پ', 'ｐ', 'פ', 'ף'],
            'q' => ['ყ', 'ｑ'],
            'r' => ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ', 'ｒ', 'ר'],
            's' => ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს', 'ｓ', 'ס'],
            't' => ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ', 'ｔ', 'ת'],
            'u' => ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'û', 'ū', 'ů',
                'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ', 'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ',
                'उ', 'ｕ', 'ў', 'ü'],
            'v'    => ['в', 'ვ', 'ϐ', 'ｖ', 'ו'],
            'w'    => ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ', 'ｗ'],
            'x'    => ['χ', 'ξ', 'ｘ'],
            'y'    => ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ', 'ｙ'],
            'z'    => ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ', 'ｚ', 'ז'],
            'aa'   => ['ع', 'आ', 'آ'],
            'ae'   => ['æ', 'ǽ'],
            'ai'   => ['ऐ'],
            'ch'   => ['ч', 'ჩ', 'ჭ', 'چ'],
            'dj'   => ['ђ', 'đ'],
            'dz'   => ['џ', 'ძ', 'דז'],
            'ei'   => ['ऍ'],
            'gh'   => ['غ', 'ღ'],
            'ii'   => ['ई'],
            'ij'   => ['ĳ'],
            'kh'   => ['х', 'خ', 'ხ'],
            'lj'   => ['љ'],
            'nj'   => ['њ'],
            'oe'   => ['ö', 'œ', 'ؤ'],
            'oi'   => ['ऑ'],
            'oii'  => ['ऒ'],
            'ps'   => ['ψ'],
            'sh'   => ['ш', 'შ', 'ش', 'ש'],
            'shch' => ['щ'],
            'ss'   => ['ß'],
            'sx'   => ['ŝ'],
            'th'   => ['þ', 'ϑ', 'θ', 'ث', 'ذ', 'ظ'],
            'ts'   => ['ц', 'ც', 'წ'],
            'ue'   => ['ü'],
            'uu'   => ['ऊ'],
            'ya'   => ['я'],
            'yu'   => ['ю'],
            'zh'   => ['ж', 'ჟ', 'ژ'],
            '(c)'  => ['©'],
            'A'    => ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ',
                'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą', 'Α', 'Ά',
                'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ',
                'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А',
                'Ǻ', 'Ǎ', 'Ａ', 'Ä'],
            'B' => ['Б', 'Β', 'ब', 'Ｂ'],
            'C' => ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ', 'Ｃ'],
            'D' => ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ', 'Ｄ'],
            'E' => ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ',
                'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ', 'Ἑ', 'Ἒ',
                'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э', 'Є', 'Ə', 'Ｅ'],
            'F' => ['Ф', 'Φ', 'Ｆ'],
            'G' => ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ', 'Ｇ'],
            'H' => ['Η', 'Ή', 'Ħ', 'Ｈ'],
            'I' => ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ',
                'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ', 'Ἷ', 'Ῐ',
                'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ', 'Ｉ'],
            'J' => ['Ｊ'],
            'K' => ['К', 'Κ', 'Ｋ'],
            'L' => ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल', 'Ｌ'],
            'M' => ['М', 'Μ', 'Ｍ'],
            'N' => ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν', 'Ｎ'],
            'O' => ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ',
                'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Ο',
                'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ', 'Ό', 'О', 'Ө', 'Ǒ', 'Ǿ', 'Ｏ', 'Ö'],
            'P' => ['П', 'Π', 'Ｐ'],
            'Q' => ['Ｑ'],
            'R' => ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ', 'Ｒ'],
            'S' => ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ', 'Ｓ'],
            'T' => ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ', 'Ｔ'],
            'U' => ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Û', 'Ū', 'Ů', 'Ű',
                'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ', 'Ｕ', 'Ў', 'Ü'],
            'V'    => ['В', 'Ｖ'],
            'W'    => ['Ω', 'Ώ', 'Ŵ', 'Ｗ'],
            'X'    => ['Χ', 'Ξ', 'Ｘ'],
            'Y'    => ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ', 'Ｙ'],
            'Z'    => ['Ź', 'Ž', 'Ż', 'З', 'Ζ', 'Ｚ'],
            'AE'   => ['Æ', 'Ǽ'],
            'Ch'   => ['Ч'],
            'Dj'   => ['Ђ'],
            'Dz'   => ['Џ'],
            'Gx'   => ['Ĝ'],
            'Hx'   => ['Ĥ'],
            'Ij'   => ['Ĳ'],
            'Jx'   => ['Ĵ'],
            'Kh'   => ['Х'],
            'Lj'   => ['Љ'],
            'Nj'   => ['Њ'],
            'Oe'   => ['Œ'],
            'Ps'   => ['Ψ'],
            'Sh'   => ['Ш', 'ש'],
            'Shch' => ['Щ'],
            'Ss'   => ['ẞ'],
            'Th'   => ['Þ', 'Θ', 'ת'],
            'Ts'   => ['Ц'],
            'Ya'   => ['Я', 'יא'],
            'Yu'   => ['Ю', 'יו'],
            'Zh'   => ['Ж'],
            ' '    => [
                "\xC2\xA0",
                "\xE2\x80\x80",
                "\xE2\x80\x81",
                "\xE2\x80\x82",
                "\xE2\x80\x83",
                "\xE2\x80\x84",
                "\xE2\x80\x85",
                "\xE2\x80\x86",
                "\xE2\x80\x87",
                "\xE2\x80\x88",
                "\xE2\x80\x89",
                "\xE2\x80\x8A",
                "\xE2\x80\xAF",
                "\xE2\x81\x9F",
                "\xE3\x80\x80",
                "\xEF\xBE\xA0",
            ],
        ];
    }

    /**
     * Determine if a given string matches the given patterns.
     *
     * @param string|array $pattern Holds the pattern to match.
     * @param string       $value   Holds the value to check.
     * @return bool Return true if the given string match, false if not.
     */
    public static function is(string|array $pattern, string $value): bool
    {
        $patterns = Arr::wrap($pattern);

        if (empty($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if ($pattern == $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the language specific replacements for the ascii method.
     *
     * @param string $language Holds the language specific replacements.
     * @return ?array Return an array of specific replacements or null.
     */
    protected static function languageSpecificCharsArray(string $language): ?array
    {
        static $languageSpecific;

        if (!isset($languageSpecific)) {
            $languageSpecific = [
                'bg' => [
                    ['х', 'Х', 'щ', 'Щ', 'ъ', 'Ъ', 'ь', 'Ь'],
                    ['h', 'H', 'sht', 'SHT', 'a', 'А', 'y', 'Y'],
                ],
                'da' => [
                    ['æ', 'ø', 'å', 'Æ', 'Ø', 'Å'],
                    ['ae', 'oe', 'aa', 'Ae', 'Oe', 'Aa'],
                ],
                'de' => [
                    ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü'],
                    ['ae', 'oe', 'ue', 'AE', 'OE', 'UE'],
                ],
                'he' => [
                    ['א', 'ב', 'ג', 'ד', 'ה', 'ו'],
                    ['ז', 'ח', 'ט', 'י', 'כ', 'ל'],
                    ['מ', 'נ', 'ס', 'ע', 'פ', 'צ'],
                    ['ק', 'ר', 'ש', 'ת', 'ן', 'ץ', 'ך', 'ם', 'ף'],
                ],
                'ro' => [
                    ['ă', 'â', 'î', 'ș', 'ț', 'Ă', 'Â', 'Î', 'Ș', 'Ț'],
                    ['a', 'a', 'i', 's', 't', 'A', 'A', 'I', 'S', 'T'],
                ],
            ];
        }

        return $languageSpecific[$language] ?? null;
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param string $value Holds the string to convert.
     * @return string Return the converted string.
     */
    public static function lower(string $value): string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param string      $title
     * @param string      $separator
     * @param string|null $language
     * @return string
     */
    public static function slug(string $title, string $separator = '-', ?string $language = 'en'): string
    {
        $title = $language ? static::ascii($title, $language) : $title;
        $flip  = $separator === '-' ? '_' : '-';
        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);
        $title = str_replace('@', $separator . 'at' . $separator, $title);
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', static::lower($title));
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string       $haystack Holds the main string to search within.
     * @param string|array $needles  Holds the substring to search for within the main string.
     * @return bool Return true if a string starts with a given substring, false if not.
     */
    public static function startsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the length of a string using mb_strlen if available,
     * otherwise falls back to strlen.
     *
     * @param string $string   The string to measure.
     * @param string $encoding The character encoding, default is 'UTF-8'.
     * @return int The length of the string.
     */
    public static function strlen(string $string, string $encoding = 'UTF-8'): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($string, $encoding);
        }

        return strlen($string);
    }

    /**
     * Checks if a string contains another string.
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The substring to search for.
     * @return bool Returns true if the substring is found, false otherwise.
     */
    public static function strContains(string $haystack, string $needle): bool
    {
        return str_contains($haystack, $needle);
    }
}
