<?php

declare(strict_types=1);

namespace Omega\Container;

use ArrayAccess;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\Source\MutableDefinitionSourceInterface;
use Omega\Container\Exceptions\ContainerAliasLogicException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use ReturnTypeWillChange;

use function array_key_exists;
use function sprintf;

/**
 * @implements ArrayAccess<string|class-string<mixed>, mixed>
 */
class Container extends AbstractContainer implements ArrayAccess
{
    /** @var array<string, string> Register aliases entry container. */
    protected array $aliases = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(
        array|MutableDefinitionSourceInterface $definitions = [],
        ?ContainerInterface $wrapperContainer = null
    ) {
        // Qui puoi fare logica custom (ContainerBuilder, cache, ecc)
        parent::__construct($definitions, $wrapperContainer);
    }

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
     *
     * @param array<array-key, mixed> $parameters Optional parameters to use to build the entry. Use this to force
     *                                            specific parameters to specific values. Parameters not defined in this
     *                                            array will be resolved using the container.
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
     * Set entry alias container.
     *
     * @param string $abstract
     * @param string $alias
     * @return void
     * @throws ContainerAliasLogicException
     */
    public function alias(string $abstract, string $alias): void
    {
        if ($abstract === $alias) {
            throw new ContainerAliasLogicException(
                sprintf(
                    "'%s' is aliased to itself.",
                    $abstract
                )
            );
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * Get alias for an abstract if available.
     */
    public function getAlias(string $abstract): string
    {
        return array_key_exists($abstract, $this->aliases)
            ? $this->getAlias($this->aliases[$abstract])
            : $abstract;
    }

    /**
     * Flush container.
     */
    public function flush(): void
    {
        $this->aliases              = [];
        $this->resolvedEntries      = [];
        $this->entriesBeingResolved = [];
    }

    /**
     * Offest exist check.
     *
     * @param string $offset
     * @return bool
     * @throws InvalidDefinitionException
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get the value.
     *
     * @param string|class-string<mixed> $offset entry name or a class name
     * @return mixed
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->make($offset);
    }

    /**
     * Set the value.
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unset the value.
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->resolvedEntries[$offset]);
    }
}
