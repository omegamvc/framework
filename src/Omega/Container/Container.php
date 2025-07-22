<?php

/**
 * Part of Omega - Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Container;

use ArrayAccess;
use DI\Container as DIContainer;
use DI\DependencyException;
use DI\NotFoundException;
use Omega\Container\Exceptions\AliasConflictException;
use ReturnTypeWillChange;

use function array_key_exists;
use function sprintf;

/**
 * Extended dependency injection container based on PHP-DI.
 * This container inherits the core functionality of PHP-DI's container
 * while adding support for entry aliasing and additional management features.
 * It also implements ArrayAccess, allowing you to access entries via array syntax.
 *
 * Example:
 * ```php
 * $container['Service'] = fn() => new Service();
 * $container->alias(Service::class, 'service');
 * $service = $container['service'];
 * ```
 *
 * @category  Omega
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @implements ArrayAccess<string|class-string<mixed>, mixed>
 */
class Container extends DIContainer implements ArrayAccess, ContainerInterface
{
    /**
     * @var array<string, string>
     * Stores alias mappings where the key is the alias and the value is the original entry name.
     */
    protected array $aliases = [];

    /**
     * {@inheritDoc}
     */
    public function get(string $id): mixed
    {
        $id = $this->getAlias($id);

        return parent::get($id);
    }

    /**
     * {@inheritDoc}
     */
    public function make(string $name, array $parameters = []): mixed
    {
        $name = $this->getAlias($name);

        return parent::make($name, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        $id = $this->getAlias($id);

        return parent::has($id);
    }

    /**
     * {@inheritdoc}
     *
     * @throws AliasConflictException If the alias is the same as the abstract.
     */
    public function alias(string $abstract, string $alias): void
    {
        if ($abstract === $alias) {
            throw new AliasConflictException(
                sprintf(
                    "Cannot register alias '%s' for '%s': alias and abstract must be different.",
                    $alias,
                    $abstract
                )
            );
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(string $abstract): string
    {
        return array_key_exists($abstract, $this->aliases)
            ? $this->getAlias($this->aliases[$abstract])
            : $abstract;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        $this->aliases              = [];
        $this->resolvedEntries      = [];
        $this->entriesBeingResolved = [];
    }

    /**
     * Determines whether the given entry exists in the container.
     *
     * @param string $offset The entry name or alias.
     * @return bool True if the entry exists, false otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Retrieves an entry from the container using array syntax.
     *
     * @param string|class-string<mixed> $offset The entry name or a class name.
     * @return mixed The resolved entry.
     * @throws DependencyException
     * @throws NotFoundException
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->make($offset);
    }

    /**
     * Sets a container entry using array syntax.
     *
     * @param string $offset The entry name.
     * @param mixed $value The value or factory.
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unsets an entry from the container using array syntax.
     *
     * Note: this only clears the resolved instance, not the actual definition.
     *
     * @param string $offset The entry name.
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->resolvedEntries[$offset]);
    }
}
