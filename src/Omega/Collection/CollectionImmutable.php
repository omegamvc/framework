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

use Omega\Collection\Exceptions\CollectionImmutableException;

/**
 * Immutable collection class that prevents modification of its items.
 *
 * This class extends the abstract immutable collection base and overrides
 * mutation methods to throw exceptions, ensuring the collection cannot be altered
 * after creation.
 *
 * @category  Omega
 * @package   Collection
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @property null $bau_1
 *
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
     * @throws CollectionImmutableException Always thrown to indicate modification is not allowed.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new CollectionImmutableException();
    }

    /**
     * {@inheritdoc}
     *
     * @throws CollectionImmutableException Always thrown to indicate modification is not allowed.
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new CollectionImmutableException();
    }
}
