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

use ArrayIterator;
use Traversable;

use function array_column;
use function array_count_values;
use function array_key_exists;
use function array_key_first;
use function array_key_last;
use function array_keys;
use function array_rand;
use function array_slice;
use function array_sum;
use function array_values;
use function call_user_func;
use function count;
use function current;
use function in_array;
use function is_array;
use function is_null;
use function is_object;
use function json_encode;
use function max;
use function min;
use function next;
use function prev;
use function var_dump;

/**
 * Abstract base class for an immutable collection.
 *
 * This class provides a foundation for collections that store key-value pairs
 * and prevent direct modification after construction. It implements common
 * collection operations such as retrieval, iteration, filtering, and
 * transformation while maintaining immutability.
 *
 * The collection supports generic key (TKey) and value (TValue) types, and
 * offers methods for safe access, querying, and manipulation without
 * altering the underlying data.
 *
 * Subclasses can extend and customize behavior while preserving the core.
 *
 * immutable nature.
 * @category  Omega
 * @package   Collection
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @implements CollectionInterface<TKey, TValue>
 */
abstract class AbstractCollectionImmutable implements CollectionInterface
{
    /**
     * The internal array holding the collection items.
     *
     * @var array<TKey, TValue>
     */
    protected array $collection = [];

    /**
     * Initializes the collection with an iterable of items.
     *
     * @param iterable<TKey, TValue> $collection Initial items for the collection.
     */
    public function __construct(array $collection)
    {
        foreach ($collection as $key => $item) {
            $this->set($key, $item);
        }
    }

    /**
     * Magic getter to access collection items by key.
     *
     * @param TKey $name The key to retrieve.
     * @return TValue|null Returns the item at the specified key or null if not found.
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Returns all items in the collection as an array.
     *
     * @return array<TKey, TValue> The entire collection as an associative array.
     */
    public function all(): array
    {
        return $this->collection;
    }

    /**
     * Converts the collection to an array.
     *
     * @return array<TKey, TValue> The collection as an array.
     */
    public function toArray(): array
    {
        return $this->collection;
    }

    /**
     * Retrieves an item by key with an optional default if key does not exist.
     *
     * @template TGetDefault
     *
     * @param TKey             $name    The key to get.
     * @param TGetDefault|null $default The default value to return if key is missing.
     * @return TValue|TGetDefault|null The item value or the default if not found.
     */
    public function get($name, $default = null)
    {
        return $this->collection[$name] ?? $default;
    }

    /**
     * Sets an item in the collection by key.
     *
     * @param TKey   $name  The key where to set the item.
     * @param TValue $value The value to set.
     * @return $this Fluent interface.
     */
    protected function set($name, $value): self
    {
        $this->collection[$name] = $value;

        return $this;
    }

    /**
     * Adds an item to the collection without specifying a key.
     *
     * @param TValue $value The value to add.
     * @return $this Fluent interface.
     */
    protected function push($value): self
    {
        $this->collection[] = $value;

        return $this;
    }

    /**
     * Checks whether the collection contains the specified key.
     *
     * @param TKey $key The key to check.
     * @return bool True if the key exists in the collection, false otherwise.
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->collection);
    }

    /**
     * Checks if the collection contains a given item.
     *
     * @param TValue $item   The item to search for.
     * @param bool   $strict Whether to use strict comparison (default false).
     * @return bool True if item is found, false otherwise.
     */
    public function contain($item, bool $strict = false): bool
    {
        return in_array($item, $this->collection, $strict);
    }

    /**
     * Returns all keys in the collection.
     *
     * @return TKey[] Array of all keys in the collection.
     */
    public function keys(): array
    {
        return array_keys($this->collection);
    }

    /**
     * Returns all values in the collection.
     *
     * @return TValue[] Array of all values in the collection.
     */
    public function items(): array
    {
        return array_values($this->collection);
    }

    /**
     * Extracts values from a collection of arrays or objects by a given key.
     *
     * @param TKey      $value The key to pluck values from.
     * @param TKey|null $key   Optional key to use as the keys in the result array.
     * @return array<TKey, TValue> An array of plucked values.
     */
    public function pluck($value, $key = null): array
    {
        $results = [];

        foreach ($this->collection as $item) {
            $itemValue = is_array($item) ? $item[$value] : $item->{$value};

            if (is_null($key)) {
                $results[] = $itemValue;
                continue;
            }

            $itemKey           = is_array($item) ? $item[$key] : $item->{$key};
            $results[$itemKey] = $itemValue;
        }

        return $results;
    }

    /**
     * Returns the number of items in the collection.
     *
     * @return int The count of items.
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * Counts the items matching a given condition.
     *
     * @param callable(TValue, TKey): bool $condition A callback function that returns true for items to count.
     * @return int The count of items that satisfy the condition.
     */
    public function countIf(callable $condition): int
    {
        $count = 0;
        foreach ($this->collection as $key => $item) {
            $doSomething = call_user_func($condition, $item, $key);

            $count += $doSomething === true ? 1 : 0;
        }

        return $count;
    }

    /**
     * Counts the occurrences of each unique value in the collection.
     *
     * @return array<TKey, int> An associative array of values and their counts.
     */
    public function countBy(): array
    {
        return array_count_values($this->collection);
    }

    /**
     * Iterates over each item in the collection and applies the given callback.
     * If the callback returns false, the iteration stops.
     *
     * @param callable(TValue, TKey): (bool|void) $callable The callback to apply.
     * @return $this Fluent interface.
     */
    public function each(callable $callable): self
    {
        foreach ($this->collection as $key => $item) {
            $doSomething = call_user_func($callable, $item, $key);

            if ($doSomething === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Dumps the internal collection for debugging purposes.
     *
     * @return $this Fluent interface.
     */
    public function dump(): self
    {
        var_dump($this->collection);

        return $this;
    }

    /**
     * Determines if any item in the collection satisfies the given condition.
     *
     * @param callable(TValue, TKey): bool $condition The condition callback.
     * @return bool True if at least one item satisfies the condition, false otherwise.
     */
    public function some(callable $condition): bool
    {
        foreach ($this->collection as $key => $item) {
            if (call_user_func($condition, $item, $key) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determines if every item in the collection satisfies the given condition.
     *
     * @param callable(TValue, TKey): bool $condition The condition callback.
     * @return bool True if all items satisfy the condition, false otherwise.
     */
    public function every(callable $condition): bool
    {
        foreach ($this->collection as $key => $item) {
            if (call_user_func($condition, $item, $key) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the JSON-encoded string representation of the collection.
     *
     * @return string JSON encoded collection.
     */
    public function json(): string
    {
        return json_encode($this->collection);
    }

    /**
     * Returns the first item in the collection or a default value if empty.
     *
     * @template TGetDefault
     *
     * @param TGetDefault|null $default Default value if collection is empty.
     * @return TValue|TGetDefault|null The first item or the default.
     */
    public function first($default = null)
    {
        $key = array_key_first($this->collection) ?? 0;

        return $this->collection[$key] ?? $default;
    }

    /**
     * Returns the first N items from the collection.
     *
     * @param positive-int $take Number of items to take.
     * @return array<TKey, TValue> Array containing the first N items.
     */
    public function firsts(int $take): array
    {
        return array_slice($this->collection, 0, $take);
    }

    /**
     * Returns the last item in the collection or a default value if empty.
     *
     * @template TGetDefault
     *
     * @param TGetDefault|null $default Default value if collection is empty.
     * @return TValue|TGetDefault|null The last item or the default.
     */
    public function last($default = null)
    {
        $key = array_key_last($this->collection);

        return $this->collection[$key] ?? $default;
    }

    /**
     * Returns the last N items from the collection.
     *
     * @param positive-int $take Number of items to take.
     * @return array<TKey, TValue> Array containing the last N items.
     */
    public function lasts(int $take): array
    {
        return array_slice($this->collection, -$take, $take);
    }

    /**
     * Returns the first key of the collection.
     *
     * @return int|string|null The first key if present, or null if the collection is empty.
     */
    public function firstKey(): int|string|null
    {
        return array_key_first($this->collection);
    }

    /**
     * Returns the last key of the collection.
     *
     * @return int|string|null The last key if present, or null if the collection is empty.
     */
    public function lastKey(): int|string|null
    {
        return array_key_last($this->collection);
    }

    /**
     * Returns the current element in the collection.
     *
     * @return TValue|null The current element or null if the internal pointer is invalid.
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * Advances the internal pointer and returns the next element.
     *
     * @return TValue|false The next element or false if there are no more elements.
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * Moves the internal pointer backward and returns the previous element.
     *
     * @return TValue|false The previous element or false if at the beginning.
     */
    public function prev()
    {
        return prev($this->collection);
    }

    /**
     * Returns a random element from the collection.
     *
     * @return TValue|null A random element or null if the collection is empty.
     */
    public function rand()
    {
        $rand = array_rand($this->collection);

        return $this->get($rand);
    }

    /**
     * Checks if the collection is empty.
     *
     * @return bool True if the collection contains no items, false otherwise.
     */
    public function isEmpty(): bool
    {
        return empty($this->collection);
    }

    /**
     * Returns the number of items in the collection.
     *
     * @return int The count of items.
     */
    public function length(): int
    {
        return count($this->collection);
    }

    /**
     * Returns the sum of all values in the collection.
     *
     * @return int The sum of values.
     */
    public function sum(): int
    {
        return array_sum($this->collection);
    }

    /**
     * Returns the average (mean) of all values in the collection.
     *
     * @return float The average value.
     */
    public function avg(): float
    {
        return $this->count() === 0 ? 0 : $this->sum() / $this->count();
    }

    /**
     * Finds the highest value in the collection or in a given key column.
     *
     * @param int|string|null $key Optional key to find max value from nested arrays/objects.
     * @return int The highest value found.
     */
    public function max(int|string|null $key = null): int
    {
        return max(array_column($this->collection, $key));
    }

    /**
     * Finds the lowest value in the collection or in a given key column.
     *
     * @param int|string|null $key Optional key to find min value from nested arrays/objects.
     * @return int The lowest value found.
     */
    public function min(int|string|null $key = null): int
    {
        return min(array_column($this->collection, $key));
    }

    /**
     * Checks if the given offset exists in the collection.
     *
     * @param TKey $offset The key to check.
     * @return bool True if the offset exists, false otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Retrieves the value at the given offset.
     *
     * @param TKey $offset The key to retrieve.
     * @return TValue|null The value at the offset, or null if not found.
     */
    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    /**
     * Sets the value at the given offset.
     *
     * @param TKey|null $offset The key to set.
     * @param TValue $value The value to set.
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unsetting values is not supported in an immutable collection.
     *
     * @param TKey $offset The key to unset.
     */
    public function offsetUnset(mixed $offset): void
    {
        // Immutable collection; no action performed.
    }

    /**
     * Returns an iterator for the collection.
     *
     * @return Traversable<TKey, TValue> Iterator for key-value pairs.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }

    /**
     * Deep clones the collection when the object is cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $this->collection = $this->deepClone($this->collection);
    }

    /**
     * Recursively clones an array of values.
     *
     * @param array<TKey, TValue> $collection The array to deep clone.
     * @return array<TKey, TValue> The deep cloned array.
     */
    protected function deepClone(array $collection): array
    {
        $clone = [];
        foreach ($collection as $key => $value) {
            if (is_array($value)) {
                $clone[$key] = $this->deepClone($value);
                continue;
            }

            if (is_object($value)) {
                $clone[$key] = clone $value;
                continue;
            }

            $clone[$key] = $value;
        }

        return $clone;
    }
}
