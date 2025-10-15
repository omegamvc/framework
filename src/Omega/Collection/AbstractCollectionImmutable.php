<?php

declare(strict_types=1);

namespace Omega\Collection;

use ArrayIterator;
use ReturnTypeWillChange;
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
 * @template TKey of array-key
 * @template TValue
 *
 * @implements CollectionInterface<TKey, TValue>
 */
abstract class AbstractCollectionImmutable implements CollectionInterface
{
    /**
     * @var array<TKey, TValue>
     */
    protected array $collection = [];

    /**
     * @param iterable<TKey, TValue> $collection
     */
    public function __construct(array $collection)
    {
        foreach ($collection as $key => $item) {
            $this->set($key, $item);
        }
    }

    /**
     * @param TKey $name
     * @return TValue|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->collection;
    }

    /**
     * @template TGetDefault
     * @param TKey             $name
     * @param TGetDefault|null $default
     * @return TValue|TGetDefault|null
     */
    public function get($name, $default = null): mixed
    {
        return $this->collection[$name] ?? $default;
    }

    /**
     * @param TKey   $name
     * @param TValue $value
     * @return $this
     */
    protected function set($name, $value): self
    {
        $this->collection[$name] = $value;

        return $this;
    }

    /**
     * Push item (set without key).
     *
     * @param TValue $value
     * @return $this
     */
    protected function push($value): self
    {
        $this->collection[] = $value;

        return $this;
    }

    /**
     * @param TKey $key
     * @return bool
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->collection);
    }

    /**
     * @param TValue $item
     * @param bool $strict
     * @return bool
     */
    public function contain($item, bool $strict = false): bool
    {
        return in_array($item, $this->collection, $strict);
    }

    /**
     * @return TKey[]
     */
    public function keys(): array
    {
        return array_keys($this->collection);
    }

    /**
     * @return TValue[]
     */
    public function items(): array
    {
        return array_values($this->collection);
    }

    /**
     * Puck given value by array key.
     *
     * @param TKey      $value Pluck key target as value
     * @param TKey|null $key   Pluck key target as key
     * @return array<TKey, TValue>
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
     * @return int
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * @param callable(TValue, TKey=): bool $condition
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
     * @return array<TKey, int>
     */
    public function countBy(): array
    {
        return array_count_values($this->collection);
    }

    /**
     * @param callable(TValue, TKey=): (bool|void) $callable
     * @return $this
     */
    public function each(callable $callable): self
    {
        foreach ($this->collection as $key => $item) {
            $doSomething = call_user_func($callable, $item, $key);

            if (false === $doSomething) {
                break;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function dump(): self
    {
        var_dump($this->collection);

        return $this;
    }

    /**
     * @param callable(TValue, TKey=): bool $condition
     * @return bool
     */
    public function some(callable $condition): bool
    {
        foreach ($this->collection as $key => $item) {
            $call = call_user_func($condition, $item, $key);

            if ($call === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param callable(TValue, TKey=): bool $condition
     * @return bool
     */
    public function every(callable $condition): bool
    {
        foreach ($this->collection as $key => $item) {
            $call = call_user_func($condition, $item, $key);

            if ($call === false) {
                return false;
            }
        }

        return true;
    }

    public function json(): string
    {
        return json_encode($this->collection);
    }

    /**
     * @template TGetDefault
     * @param TGetDefault|null $default
     * @return TValue|TGetDefault|null
     */
    public function first($default = null): mixed
    {
        $key = array_key_first($this->collection) ?? 0;

        return $this->collection[$key] ?? $default;
    }

    /**
     * @param positive-int $take
     * @return array<TKey, TValue>
     */
    public function firsts(int $take): array
    {
        return array_slice($this->collection, 0, (int) $take);
    }

    /**
     * @template TGetDefault
     * @param TGetDefault|null $default
     * @return TValue|TGetDefault|null
     */
    public function last($default = null): mixed
    {
        $key = array_key_last($this->collection);

        return $this->collection[$key] ?? $default;
    }

    /**
     * @param positive-int $take
     * @return array<TKey, TValue>
     */
    public function lasts(int $take): array
    {
        return array_slice($this->collection, -$take, (int) $take);
    }

    /**
     * @return TKey|null
     */
    public function firstKey(): mixed
    {
        return array_key_first($this->collection);
    }

    /**
     * @return TKey|null
     */
    public function lastKey(): mixed
    {
        return array_key_last($this->collection);
    }

    /**
     * @return TValue
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * @return TValue
     */
    public function next()
    {
        return next($this->collection);
    }

    /**
     * @return TValue
     */
    public function prev()
    {
        return prev($this->collection);
    }

    /**
     * @return TValue
     */
    public function rand(): mixed
    {
        $rand = array_rand($this->collection);

        return $this->get($rand);
    }

    public function isEmpty(): bool
    {
        return empty($this->collection);
    }

    public function length(): int
    {
        return count($this->collection);
    }

    public function sum(): int
    {
        return array_sum($this->collection);
    }

    public function avg(): int
    {
        return $this->sum() / $this->count();
    }

    /**
     * Find the highest value.
     *
     * @param int|string|null $key
     * @return int
     */
    public function max(int|string|null $key = null): int
    {
        return max(array_column($this->collection, $key));
    }

    /**
     * Find lowest value.
     *
     * @param int|string|null $key
     * @return int
     */
    public function min(int|string|null $key = null): int
    {
        return min(array_column($this->collection, $key));
    }

    /**
     * @param TKey $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @param TKey $offset
     * @return TValue|null
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
    }

    /**
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }

    public function __clone()
    {
        $this->collection = $this->deepClone($this->collection);
    }

    /**
     * @param array<TKey, TValue> $collection
     * @return array<TKey, TValue>
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
