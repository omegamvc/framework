<?php

declare(strict_types=1);

namespace Omega\Collection;

use Omega\Collection\Exception\ImmutableCollectionException;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @extends AbstractCollectionImmutable<TKey, TValue>
 */
class CollectionImmutable extends AbstractCollectionImmutable
{
    /**
     * {@inheritdoc}
     *
     * @throws NoModify
     */
    public function offsetSet($offset, $value): void
    {
        throw new NoModify();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NoModify
     */
    public function offsetUnset($offset): void
    {
        throw new NoModify();
    }
}
