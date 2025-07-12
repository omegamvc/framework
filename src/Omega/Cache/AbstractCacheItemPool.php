<?php

/**
 * Part of Omega - Cache Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache;

use DateInterval;
use Generator;
use Traversable;
use Omega\Cache\Exception\InvalidArgumentException;
use Omega\Cache\Item\CacheItemInterface;
use Omega\Cache\Item\HasExpirationDateInterface;

use function get_class;
use function iterator_to_array;
use function sprintf;

/**
 * Abstract class for a cache item pool.
 *
 * This class serves as a base implementation for cache pools, handling common functionalities
 * such as option management, deferred storage, and batch retrieval of cache items. It provides
 * a foundation for concrete cache implementations that comply with PSR-6 and PSR-16 standards.
 *
 * @category   Omega
 * @package    Cache
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
abstract class AbstractCacheItemPool implements CacheItemPoolInterface, CacheInterface
{
    /**
     * Deferred cache items waiting to be committed.
     *
     * @var CacheItemInterface[] Array of cache items that have been deferred for storage.
     */
    private array $deferred = [];

    /**
     * Constructor.
     *
     * @param array<string, mixed> $options Configuration options for the cache pool.
     * @return void
     * @throws InvalidArgumentException If the provided options are not an array.
     */
    public function __construct(protected array $options = [])
    {
        if (!is_array($options)) {
            throw new InvalidArgumentException(sprintf('%s requires an options array', get_class($this)));
        }
    }

    /**
     * {@inheritdoc}
     * @return array<string, CacheItemInterface>
     */
    public function getItems(array $keys = []): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->getItem($key);
        }

        return $result;
    }

    /**
     * Retrieves a configuration option from the cache instance.
     *
     * @param string $key The name of the option to retrieve.
     * @return mixed The value of the requested option, or null if it does not exist.
     */
    public function getOption(string $key): mixed
    {
        return $this->options[$key] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->deleteItem($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sets a configuration option for the cache instance.
     *
     * @param string $key   The name of the option to set.
     * @param mixed  $value The value to assign to the option.
     * @return $this Returns the current instance for method chaining.
     */
    public function setOption(string $key, mixed $value): self
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[$item->getKey()] = $item;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        $result = true;

        foreach ($this->deferred as $key => $deferred) {
            $saveResult = $this->save($deferred);

            if (true === $saveResult) {
                unset($this->deferred[$key]);
            }

            $result = $result && $saveResult;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $item = $this->getItem($key);

        if (!$item->isHit()) {
            return $default;
        }

        return $item->get();
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $item = $this->getItem($key);
        $item->set($value);
        $item->expiresAfter($ttl);

        return $this->save($item);
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool
    {
        return $this->deleteItem($key);
    }

    /**
     * Retrieves multiple cache items by their unique keys.
     *
     * @param iterable<string> $keys    Holds a list of cache keys to retrieve.
     * @param mixed            $default Holds the default value to return for keys that do not exist or have expired.
     * @return iterable<string, mixed> A list of key-value pairs, where non-existing keys are mapped to the default
     *                                 value.
     * @throws InvalidArgumentException If the provided keys are not a valid iterable.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        if (!is_array($keys)) {
            if (!($keys instanceof Traversable)) {
                throw new InvalidArgumentException(
                    '$keys is neither an array nor Traversable'
                );
            }

            $keys = iterator_to_array($keys, false);
        }

        $items = $this->getItems($keys);

        return $this->generateValues($default, $items);
    }

    /**
     * {@inheritdoc}
     * @param iterable<string, mixed> $values Values to be set in cache.
     * @throws InvalidArgumentException
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        if (!is_array($values)) {
            if (!$values instanceof Traversable) {
                throw new InvalidArgumentException(
                    '$values is neither an array nor Traversable'
                );
            }
        }

        $keys        = [];
        $arrayValues = [];

        foreach ($values as $key => $value) {
            // @phpstan-ignore-next-line
            if (is_int($key)) {
                $key = (string) $key;
            }

            $keys[]            = $key;
            $arrayValues[$key] = $value;
        }

        $items       = $this->getItems($keys);
        $itemSuccess = true;

        foreach ($items as $key => $item) {
            $item->set($arrayValues[$key]);
            $item->expiresAfter($ttl);

            $itemSuccess = $itemSuccess && $this->saveDeferred($item);
        }

        return $itemSuccess && $this->commit();
    }

    /**
     * {@iheritdoc}
     * @throws InvalidArgumentException
     */
    public function deleteMultiple(iterable $keys): bool
    {
        if (!is_array($keys)) {
            if (!$keys instanceof Traversable) {
                throw new InvalidArgumentException(
                    '$keys is neither an array nor Traversable'
                );
            }

            $keys = iterator_to_array($keys, false);
        }

        return $this->deleteItems($keys);
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return $this->hasItem($key);
    }

    /**
     * Converts the expiration date of a cache item into seconds from the current time.
     *
     * @param HasExpirationDateInterface $item The cache item to evaluate.
     * @return int The number of seconds remaining until expiration.
     */
    protected function convertItemExpiryToSeconds(HasExpirationDateInterface $item): int
    {
        return $item->getExpiration()->getTimestamp() - time();
    }

  /**
     * Generates key-value pairs for the PSR-16 `getMultiple` method.
     *
     * @param mixed                             $default The default value to return for missing cache keys.
     * @param array<string, CacheItemInterface> $items   An array of cache items to process.
     * @return Generator Yields key-value pairs, where expired or missing items return the default value.
     */
    private function generateValues(mixed $default, array $items): Generator
    {
        foreach ($items as $key => $item) {
            if (!$item->isHit()) {
                yield $key => $default;
            } else {
                yield $key => $item->get();
            }
        }
    }
}
