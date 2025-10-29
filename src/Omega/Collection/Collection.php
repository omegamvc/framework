<?php

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

use const INF;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends AbstractCollectionImmutable<TKey, TValue>
 *
 * @property mixed $buah_1
 * @property mixed $buah_8
 */
class Collection extends AbstractCollectionImmutable
{
    /**
     * @param TKey   $name
     * @param TValue $value
     */
    public function __set($name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Add reference from collection.
     *
     * @param AbstractCollectionImmutable<TKey, TValue> $collection
     * @return $this
     */
    public function ref(AbstractCollectionImmutable $collection): self
    {
        $this->add($collection->collection);

        return $this;
    }

    /**
     * @return $this
     */
    public function clear(): self
    {
        $this->collection = [];

        return $this;
    }

    /**
     * @param array<TKey, TValue> $collection
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
     * @param TKey $name
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
     * @param TKey   $name
     * @param TValue $value
     * @return $this
     */
    public function set($name, $value): self
    {
        parent::set($name, $value);

        return $this;
    }

    /**
     * Push item (set without key).
     *
     * @param TValue $value
     * @return $this
     */
    public function push($value): self
    {
        parent::push($value);

        return $this;
    }

    /**
     * @param array<TKey, TValue> $newCollection
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
     * @param callable(TValue, TKey=): TValue $callable
     *
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
     * @param callable(TValue, TKey=): bool $condition
     * @return $this
     */
    public function filter(callable $condition): self
    {
        return $this->filterByCondition($condition);
    }

    /**
     * @param callable(TValue, TKey=): bool $condition
     * @return $this
     */
    public function reject(callable $condition): self
    {
        return $this->filterByCondition($condition, true);
    }

    private function filterByCondition(callable $condition, bool $invert = false): self
    {
        $newCollection = [];
        foreach ($this->collection as $key => $item) {
            $result = $condition($item, $key);
            if ($invert ? !$result : $result) {
                $newCollection[$key] = $item;
            }
        }
        return $this->replace($newCollection);
    }

    /**
     * @return $this
     */
    public function reverse(): self
    {
        return $this->replace(array_reverse($this->collection));
    }

    /**
     * @return $this
     */
    public function sort(): self
    {
        asort($this->collection);

        return $this;
    }

    /**
     * @return $this
     */
    public function sortDesc(): self
    {
        arsort($this->collection);

        return $this;
    }

    /**
     * @return $this
     */
    public function sortBy(callable $callable): self
    {
        uasort($this->collection, $callable);

        return $this;
    }

    /**
     * @return $this
     */
    public function sortByDesc(callable $callable): self
    {
        return $this->sortBy($callable)->reverse();
    }

    /**
     * @return $this
     */
    public function sortKey(): self
    {
        ksort($this->collection);

        return $this;
    }

    /**
     * @return $this
     */
    public function sortKeyDesc(): self
    {
        krsort($this->collection);

        return $this;
    }

    /**
     * @return Collection<TKey, TValue>
     */
    public function clone(): Collection
    {
        return clone $this;
    }

    /**
     * @return $this
     */
    public function chunk(int $length, bool $preserveKeys = true): self
    {
        $this->collection = array_chunk($this->collection, $length, $preserveKeys);

        return $this;
    }

    /**
     * @return $this
     */
    public function split(int $count, bool $preserveKeys = true): self
    {
        $length = (int) ceil($this->length() / $count);

        return $this->chunk($length, $preserveKeys);
    }

    /**
     * @param TKey[] $excepts
     * @return $this
     */
    public function except(array $excepts): self
    {
        $this->filter(fn ($item, $key) => !in_array($key, $excepts));

        return $this;
    }

    /**
     * @param TKey[] $only
     * @return $this
     */
    public function only(array $only): self
    {
        /* @phpstan-ignore-next-line */
        $this->filter(fn ($item, $key) => in_array($key, $only));

        return $this;
    }

    /**
     * @param int|float $depth
     * @return $this
     */
    public function flatten(int|float $depth = INF): self
    {
        $flatten = $this->flattenRecursing($this->collection, $depth);
        $this->replace($flatten);

        return $this;
    }

    /**
     * @param array<TKey, TValue> $array
     * @param float|int $depth
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
     * @return CollectionImmutable<TKey, TValue>
     */
    public function immutable(): CollectionImmutable
    {
        return new CollectionImmutable($this->collection);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        $this->remove($offset);
    }

    /**
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
     * Convert array, key and value from item (also key).
     *
     * @template TKeyItem of array-key
     * @template TValueItem
     * @param callable(TValue, TKey=): array<TKeyItem, TValueItem> $callable With single key/value pair per element
     * @return $this
     */
    public function assocBy(callable $callable): self
    {
        /** @var array<TKeyItem, TValueItem> $newCollectionn */
        $newCollection = [];
        foreach ($this->collection as $key => $item) {
            $arrayAssoc          = $callable($item, $key);
            $key                 = array_key_first($arrayAssoc);
            $newCollection[$key] = $arrayAssoc[$key];
        }

        return $this->replace($newCollection);
    }

    /**
     * Reduce items.
     *
     * @param callable(TValue, TValue): TValue $callable
     * @param TValue|null                      $carry
     * @return TValue|null
     */
    public function reduce(callable $callable, $carry = null): mixed
    {
        foreach ($this->collection as $item) {
            $carry = $callable($carry, $item);
        }

        return $carry;
    }

    /**
     * @param int $limit
     * @return $this
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
     * @param array<TKey, TValue> $collection
     * @return $this
     */
    public function diff(array $collection): self
    {
        return $this->replace(
            array_diff($this->collection, $collection)
        );
    }

    /**
     * @param array<TKey, TValue> $collection
     * @return $this
     */
    public function diffKeys(array $collection): self
    {
        return $this->replace(
            array_diff_key($this->collection, $collection)
        );
    }

    /**
     * @param array<TKey, TValue> $collection
     * @return $this
     */
    public function diffAssoc(array $collection): self
    {
        return $this->replace(
            array_diff_assoc($this->collection, $collection)
        );
    }

    /**
     * @param array<TKey, TValue> $collection
     * @return $this
     */
    public function complement(array $collection): self
    {
        return $this->replace(
            array_diff($collection, $this->collection)
        );
    }

    /**
     * @param array<TKey, TValue> $collection
     * @return $this
     */
    public function complementKeys(array $collection): self
    {
        return $this->replace(
            array_diff_key($collection, $this->collection)
        );
    }

    /**
     * @param array<TKey, TValue> $collection
     * @return $this
     */
    public function complementAssoc(array $collection): self
    {
        return $this->replace(
            array_diff_assoc($collection, $this->collection)
        );
    }

    /**
     * Filter where using operator.
     *
     * @param TKey   $key
     * @param TValue $value
     * @return $this
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
     * Filter where in range.
     *
     * @param TKey     $key
     * @param TValue[] $range
     * @return $this
     */
    public function whereIn($key, array $range): self
    {
        return $this->filter(fn ($TValue) => array_key_exists($key, $TValue) && in_array($TValue[$key], $range));
    }

    /**
     * Filter where not in range.
     *
     * @param TKey     $key
     * @param TValue[] $range
     * @return $this
     */
    public function whereNotIn($key, array $range): self
    {
        return $this->filter(
            fn ($TValue) => array_key_exists($key, $TValue) && false === in_array($TValue[$key], $range)
        );
    }
}
