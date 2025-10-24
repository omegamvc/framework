<?php

declare(strict_types=1);

namespace Omega\Support;

use Omega\Environment\Dotenv\Dotenv;

use function array_key_exists;
use function is_numeric;
use function strtolower;

class Env
{
    protected static array $values = [];

    public static function load(string $path, string $file = '.env'): void
    {
        $dotenv = Dotenv::createImmutable($path, $file);
        self::$values = $dotenv->load();
    }

/**    public static function get(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, self::$values)) {
            return $default;
        }

        $value = self::$values[$key];

        return match (strtolower($value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)'   => null,
            default            => is_numeric($value) ? $value + 0 : $value,
        };
    }*/

    /**public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$values[$key] ?? getenv($key) ?? $default;

        return match (strtolower((string) $value)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)'   => null,
            default            => is_numeric($value) ? $value + 0 : $value,
        };
    }*/

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::$values[$key] ?? getenv($key) ?: $default;

        // Se il valore è una stringa, normalizza alcuni valori speciali
        if (is_string($value)) {
            $lower = strtolower($value);
            return match ($lower) {
                'true', '(true)'   => true,
                'false', '(false)' => false,
                'null', '(null)'   => null,
                'empty', '(empty)' => '',
                default            => is_numeric($value) ? $value + 0 : $value,
            };
        }

        // Tutti gli altri tipi (int, bool, null) vengono restituiti così come sono
        return $value;
    }
}
