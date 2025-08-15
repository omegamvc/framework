<?php

declare(strict_types=1);

namespace Omega\Collection;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends ArrayAccess<TKey, TValue>
 * @extends IteratorAggregate<TKey, TValue>
 */
interface CollectionInterface extends ArrayAccess, Countable, IteratorAggregate
{
    /** @return array<TKey, TValue> */
    public function toArray(): array;
}
