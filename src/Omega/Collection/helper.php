<?php

/**
 * Part of Omega - Collection Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Collection;

use function array_key_exists;
use function array_slice;
use function count;
use function explode;
use function function_exists;
use function implode;

/**
 * Helper functions for working with collections and array data.
 *
 * This file provides convenient global functions to create mutable and immutable collections,
 * as well as to access nested array data using dot notation.
 *
 * @category  Omega
 * @package   Collection
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

if (!function_exists('collection')) {
    /**
     * Create a new mutable Collection instance from an iterable.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey, TValue> $collection Iterable of items to initialize the collection
     * @return Collection<TKey, TValue> A new mutable Collection object containing the given items
     */
    function collection(iterable $collection = []): Collection
    {
        return new Collection($collection);
    }
}

if (!function_exists('collection_immutable')) {
    /**
     * Create a new immutable Collection instance from an iterable.
     *
     * Note: Currently returns a mutable Collection instance; should return an immutable variant if implemented.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param iterable<TKey, TValue> $collection Iterable of items to initialize the collection
     * @return Collection<TKey, TValue> A new immutable Collection object containing the given items
     */
    function collection_immutable(iterable $collection = []): Collection
    {
        return new Collection($collection);
    }
}

if (!function_exists('data_get')) {
    /**
     * Retrieve a value from a nested array using dot notation keys.
     *
     * Supports '*' wildcard segments to retrieve values from multiple nested arrays.
     *
     * @template TValue
     * @template TGetDefault
     *
     * @param array<array-key, TValue> $array The array to search
     * @param array-key|string         $key   Dot notation string key (e.g. 'user.profile.name')
     * @param TGetDefault              $default Default value to return if the key does not exist
     * @return TGetDefault|array<array-key, TValue>|null The value found at the given key or the default
     */
    function data_get(array $array, int|string $key, $default = null)
    {
        $segments = explode('.', (string) $key);
        foreach ($segments as $segment) {
            if (array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } elseif ('*' === $segment) {
                $values = [];
                foreach ($array as $item) {
                    /** @phpstan-ignore-next-line */
                    $value = data_get($item, implode('.', array_slice($segments, 1)));
                    if (null !== $value) {
                        $values[] = $value;
                    }
                }

                return count($values) > 0 ? $values : $default;
            } else {
                return $default;
            }
        }

        return $array;
    }
}
