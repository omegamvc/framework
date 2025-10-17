<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Helper;

use Omega\Container\Definition\DecoratorDefinition;
use Omega\Container\Definition\FactoryDefinition;

/**
 * Helps to define how to create an instance of a class using a factory (callable).
 */
class FactoryDefinitionHelper implements DefinitionHelperInterface
{
    /** @var callable*/
    private $factory;

    /** @var bool  */
    private bool $decorate;

    /** @var array  */
    private array $parameters = [];

    /**
     * @param callable|array|string $factory
     * @param bool                  $decorate Is the factory decorating a previous definition?
     */
    public function __construct(callable|array|string $factory, bool $decorate = false)
    {
        $this->factory  = $factory;
        $this->decorate = $decorate;
    }

    public function getDefinition(string $entryName): FactoryDefinition
    {
        if ($this->decorate) {
            return new DecoratorDefinition($entryName, $this->factory, $this->parameters);
        }

        return new FactoryDefinition($entryName, $this->factory, $this->parameters);
    }

    /**
     * Defines arguments to pass to the factory.
     *
     * Because factory methods do not yet support attributes or autowiring, this method
     * should be used to define all parameters except the ContainerInterface and RequestedEntry.
     *
     * Multiple calls can be made to the method to override individual values.
     *
     * @param string $parameter Name or index of the parameter for which the value will be given.
     * @param mixed  $value     Value to give to this parameter.
     *
     * @return $this
     */
    public function parameter(string $parameter, mixed $value): self
    {
        $this->parameters[$parameter] = $value;

        return $this;
    }
}
