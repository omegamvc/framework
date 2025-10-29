<?php

declare(strict_types=1);

namespace Omega\Collection;

use Omega\Collection\Exceptions\ImmutableCollectionException;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends AbstractCollectionImmutable<TKey, TValue>
 *
 * @property mixed $buah_1
 */
class CollectionImmutable extends AbstractCollectionImmutable
{
    /**
     * {@inheritdoc}
     *
     * @throws ImmutableCollectionException
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new ImmutableCollectionException();
    }

    /**
     * {@inheritdoc}
     *
     * @throws ImmutableCollectionException
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new ImmutableCollectionException();
    }
}
