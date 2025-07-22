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

use function array_chunk;
use function array_diff;
use function array_diff_assoc;
use function array_diff_key;
use function array_key_exists;
use function array_key_first;
use function array_reverse;
use function array_slice;
use function array_values;
use function arsort;
use function asort;
use function call_user_func;
use function ceil;
use function in_array;
use function is_array;
use function is_callable;
use function krsort;
use function ksort;
use function shuffle;
use function uasort;

/**
 * Collection class that provides a fluent, convenient wrapper around arrays.
 *
 * This class offers a rich set of methods to manipulate and query collections
 * of items in an immutable or mutable style, including filtering, reducing,
 * slicing, and set operations like differences and complements.
 *
 * It supports generic keys and values, making it flexible for various data structures.
 *
 * Typical use cases include data transformation pipelines, filtering datasets,
 * and performing complex array operations with readable, chainable syntax.
 *
 * @category  Omega
 * @package   Collection
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @property string|null $bau_1
 * @property string|null $bau_8
 * @template TKey of array-key
 * @template TValue
 *
 * @extends AbstractCollectionImmutable<TKey, TValue>
 */
class Collection extends AbstractCollectionImmutable
{
    /**
     * Magic setter that delegates to the set() method.
     *
     * @param TKey   $name  The key to set in the collection.
     * @param TValue $value The value to assign.
     * @return void
     */
    public function __set($name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Adds all items from another collection by reference.
     *
     * @param AbstractCollectionImmutable<TKey, TValue> $collection The collection to reference.
     * @return $this
     */
    public function ref(AbstractCollectionImmutable $collection): self
    {
        $this->add($collection->collection);

        return $this;
    }

    /**
     * Clears all items from the collection.
     *
     * @return $this
     */
    public function clear(): self
    {
        $this->collection = [];

        return $this;
    }

    /**
     * Adds items from an array to the collection.
     *
     * @param array<TKey, TValue> $collection Items to add.
     * @return $this
     */
    public function add(array $collection): self
    {
        foreach ($collection as $key => $item) {
            $this->set($key, $item);
        }

        return $this;
    }

    /**
     * Removes an item by key if it exists.
     *
     * @param TKey $name The key of the item to remove.
     * @return $this
     */
    public function remove($name): self
    {
        if ($this->has($name)) {
            unset($this->collection[$name]);
        }

        return $this;
    }

    /**
     * Sets a value by key in the collection.
     *
     * @param TKey   $name  The key where the value is stored.
     * @param TValue $value The value to set.
     * @return $this
     */
    public function set($name, $value): self
    {
        parent::set($name, $value);

        return $this;
    }

    /**
     * Adds a value to the collection without specifying a key.
     *
     * @param TValue $value The value to push.
     * @return $this
     */
    public function push($value): self
    {
        parent::push($value);

        return $this;
    }

    /**
     * Replaces the entire collection with a new one.
     *
     * @param array<TKey, TValue> $newCollection The new collection to replace with.
     * @return $this
     */
    public function replace(array $newCollection): self
    {
        $this->collection = [];
        foreach ($newCollection as $key => $item) {
            $this->set($key, $item);
        }

        return $this;
    }

    /**
     * Applies a callback to each item, replacing each with the callback's result.
     *
     * @param callable(TValue, TKey=): TValue $callable Function to apply to each item.
     * @return $this
     */
    public function map(callable $callable): self
    {
        if (!is_callable($callable)) {
            return $this;
        }

        $newCollection = [];
        foreach ($this->collection as $key => $item) {
            $newCollection[$key] = call_user_func($callable, $item, $key);
        }

        $this->replace($newCollection);

        return $this;
    }

    /**
     * Filters the collection based on a callable condition.
     *
     * @param callable(TValue, TKey=): bool $condition_true Callable to evaluate each item.
     * @param bool                         $includeWhenTrue Whether to include items where condition is true.
     * @return $this
     */
    private function filterByCondition(callable $condition_true, bool $includeWhenTrue): self
    {
        if (!is_callable($condition_true)) {
            return $this;
        }

        $newCollection = [];
        foreach ($this->collection as $key => $item) {
            $condition = $condition_true($item, $key);

            if ($condition === $includeWhenTrue) {
                $newCollection[$key] = $item;
            }
        }

        $this->replace($newCollection);

        return $this;
    }

    /**
     * Filters the collection by including only items that satisfy the condition.
     *
     * @param callable(TValue, TKey=): bool $conditionTrue Callback that returns true for items to keep.
     * @return $this
     */
    public function filter(callable $conditionTrue): self
    {
        return $this->filterByCondition($conditionTrue, true);
    }

    /**
     * Filters the collection by excluding items that satisfy the condition.
     *
     * @param callable(TValue, TKey=): bool $conditionTrue Callback that returns true for items to reject.
     * @return $this
     */
    public function reject(callable $conditionTrue): self
    {
        return $this->filterByCondition($conditionTrue, false);
    }

    /**
     * Reverses the order of items in the collection.
     *
     * @return $this
     */
    public function reverse(): self
    {
        return $this->replace(array_reverse($this->collection));
    }

    /**
     * Sorts the collection by values in ascending order, preserving keys.
     *
     * @return $this
     */
    public function sort(): self
    {
        asort($this->collection);

        return $this;
    }

    /**
     * Sorts the collection by values in descending order, preserving keys.
     *
     * @return $this
     */
    public function sortDesc(): self
    {
        arsort($this->collection);

        return $this;
    }

    /**
     * Sorts the collection using a user-defined comparison callback.
     *
     * @param callable(TValue, TValue): int $callable Comparison function for sorting.
     * @return $this
     */
    public function sortBy(callable $callable): self
    {
        uasort($this->collection, $callable);

        return $this;
    }

    /**
     * Sorts the collection in descending order using a user-defined comparison callback.
     *
     * @param callable(TValue, TValue): int $callable Comparison function for sorting.
     * @return $this
     */
    public function sortByDesc(callable $callable): self
    {
        return $this->sortBy($callable)->reverse();
    }

    /**
     * Sorts the collection by keys in ascending order.
     *
     * @return $this
     */
    public function sortKey(): self
    {
        ksort($this->collection);

        return $this;
    }

    /**
     * Sorts the collection by keys in descending order.
     *
     * @return $this
     */
    public function sortKeyDesc(): self
    {
        krsort($this->collection);

        return $this;
    }

    /**
     * Creates a shallow clone of the collection object.
     *
     * @return Collection<TKey, TValue>
     */
    public function clone(): Collection
    {
        return clone $this;
    }

    /**
     * Splits the collection into chunks of the given length.
     *
     * @param int  $length       Number of items per chunk.
     * @param bool $preserveKeys Whether to preserve original keys.
     * @return $this
     */
    public function chunk(int $length, bool $preserveKeys = true): self
    {
        $this->collection = array_chunk($this->collection, $length, $preserveKeys);

        return $this;
    }

    /**
     * Splits the collection into a specified number of chunks.
     *
     * @param int  $count        Number of chunks to split into.
     * @param bool $preserveKeys Whether to preserve original keys.
     * @return $this
     */
    public function split(int $count, bool $preserveKeys = true): self
    {
        $length = (int) ceil($this->length() / $count);

        return $this->chunk($length, $preserveKeys);
    }

    /**
     * Removes items from the collection by their keys.
     *
     * @param array<TKey> $excepts List of keys to exclude.
     * @return $this
     */
    public function except(array $excepts): self
    {
        /* @phpstan-ignore-next-line */
        $this->filter(fn ($item, $key) => !in_array($key, $excepts));

        return $this;
    }

    /**
     * Keeps only the items in the collection with the specified keys.
     *
     * @param array<TKey> $only List of keys to include.
     * @return $this
     */
    public function only(array $only): self
    {
        /* @phpstan-ignore-next-line */
        $this->filter(fn ($item, $key) => in_array($key, $only));

        return $this;
    }

    /**
     * Flattens a multi-dimensional collection into a single level up to the specified depth.
     *
     * @param float|int $depth Maximum depth to flatten (default is infinite).
     * @return $this
     */
    public function flatten(float|int $depth = INF): self
    {
        $flatten = $this->flattenRecursing($this->collection, $depth);
        $this->replace($flatten);

        return $this;
    }

    /**
     * Recursively flattens an array up to the given depth.
     *
     * @param array<TKey, TValue> $array Array to flatten.
     * @param float|int           $depth Depth level to flatten.
     * @return array<TKey, TValue>
     */
    private function flattenRecursing(array $array, float|int $depth = INF): array
    {
        $result = [];

        foreach ($array as $key => $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (!is_array($item)) {
                $result[$key] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : $this->flattenRecursing($item, $depth - 1);

                foreach ($values as $keyDept => $value) {
                    $result[$keyDept] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Returns an immutable copy of the current collection.
     *
     * @return CollectionImmutable<TKey, TValue>
     */
    public function immutable(): CollectionImmutable
    {
        return new CollectionImmutable($this->collection);
    }

    /**
     * Removes the item at the specified offset.
     *
     * @param mixed $offset The key to remove.
     * @return void
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
     * Randomly shuffles the items in the collection, preserving keys.
     *
     * @return $this
     */
    public function shuffle(): self
    {
        $items = $this->collection;
        $keys  = $this->keys();
        shuffle($keys);
        $reordered = [];
        foreach ($keys as $key) {
            $reordered[$key] = $items[$key];
        }

        return $this->replace($reordered);
    }

    /**
     * Converts the collection into an associative array by mapping each item to a key-value pair.
     *
     * @template TKeyItem of array-key
     * @template TValueItem
     *
     * @param callable(TValue, TKey=): array<TKeyItem, TValueItem> $callable Callback returning a single key-value pair.
     * @return $this
     */
    public function assocBy(callable $callable): self
    {
        /** @var array<TKeyItem, TValueItem> $newCollection */
        $newCollection = [];
        foreach ($this->collection as $key => $item) {
            $array_assoc          = $callable($item, $key);
            $key                  = array_key_first($array_assoc);
            $newCollection[$key] = $array_assoc[$key];
        }

        return $this->replace($newCollection);
    }

    /**
     * Reduce the collection to a single value by iteratively applying a callback.
     *
     * @param callable(TValue|null, TValue): TValue $callable Callback to apply on carry and item.
     * @param TValue|null $carry Initial value to carry over; null by default.
     * @return TValue|null The final reduced value after processing all items.
     */
    public function reduce(callable $callable, $carry = null)
    {
        foreach ($this->collection as $item) {
            $carry = $callable($carry, $item);
        }

        return $carry;
    }

    /**
     * Return a new collection containing the first or last N items.
     *
     * @param int $limit Number of items to take. If negative, takes from the end.
     * @return $this New collection with the limited subset of items.
     */
    public function take(int $limit): self
    {
        if ($limit < 0) {
            return $this->replace(
                array_slice($this->collection, $limit, abs($limit))
            );
        }

        return $this->replace(
            array_slice($this->collection, 0, $limit)
        );
    }

    /**
     * Return a new collection containing items that are in this collection but not in the given array.
     *
     * @param array<TKey, TValue> $collection The array to compare against.
     * @return $this New collection with the difference of values.
     */
    public function diff(array $collection): self
    {
        return $this->replace(
            array_diff($this->collection, $collection)
        );
    }

    /**
     * Return a new collection containing items whose keys are in this collection but not in the given array.
     *
     * @param array<TKey, TValue> $collection The array to compare keys against.
     * @return $this New collection with the difference of keys.
     */
    public function diffKeys(array $collection): self
    {
        return $this->replace(
            array_diff_key($this->collection, $collection)
        );
    }

    /**
     * Return a new collection containing items whose key and value pairs are in this collection but not in the given array.
     *
     * @param array<TKey, TValue> $collection The array to compare keys and values against.
     * @return $this New collection with the difference of key-value pairs.
     */
    public function diffAssoc(array $collection): self
    {
        return $this->replace(
            array_diff_assoc($this->collection, $collection)
        );
    }

    /**
     * Return a new collection containing items that are in the given array but not in this collection.
     *
     * @param array<TKey, TValue> $collection The array to compare against.
     * @return $this New collection with the complement of values.
     */
    public function complement(array $collection): self
    {
        return $this->replace(
            array_diff($collection, $this->collection)
        );
    }

    /**
     * Return a new collection containing items whose keys are in the given array but not in this collection.
     *
     * @param array<TKey, TValue> $collection The array to compare keys against.
     * @return $this New collection with the complement of keys.
     */
    public function complementKeys(array $collection): self
    {
        return $this->replace(
            array_diff_key($collection, $this->collection)
        );
    }

    /**
     * Return a new collection containing items whose key-value pairs are in the given array but not in this collection.
     *
     * @param array<TKey, TValue> $collection The array to compare key-value pairs against.
     * @return $this New collection with the complement of key-value pairs.
     */
    public function complementAssoc(array $collection): self
    {
        return $this->replace(
            array_diff_assoc($collection, $this->collection)
        );
    }

    /**
     * Filter the collection by a key, operator, and value.
     *
     * Supports operators: '=', '==', '===', '!=', '!==', '>', '>=', '<', '<='.
     *
     * @param TKey   $key The key to filter by.
     * @param string $operator The comparison operator.
     * @param TValue $value The value to compare against.
     * @return $this New collection filtered by the condition.
     */
    public function where($key, string $operator, $value): self
    {
        if ('=' === $operator || '==' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] == $value);
        }
        if ('===' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] === $value);
        }
        if ('!=' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] != $value);
        }
        if ('!==' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] !== $value);
        }
        if ('>' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] > $value);
        }
        if ('>=' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] >= $value);
        }
        if ('<' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] < $value);
        }
        if ('<=' === $operator) {
            return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && $TValue[$key] <= $value);
        }

        return $this->replace([]);
    }

    /**
     * Filter the collection by a key where the value is in the given range array.
     *
     * @param TKey     $key The key to filter by.
     * @param TValue[] $range Array of values to match against.
     * @return $this New collection filtered where values are in range.
     */
    public function whereIn($key, array $range): self
    {
        return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && in_array($TValue[$key], $range));
    }

    /**
     * Filter the collection by a key where the value is NOT in the given range array.
     *
     * @param TKey     $key The key to filter by.
     * @param TValue[] $range Array of values to exclude.
     * @return $this New collection filtered where values are NOT in range.
     */
    public function whereNotIn($key, array $range): self
    {
        return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && false === in_array($TValue[$key], $range));
    }
}
