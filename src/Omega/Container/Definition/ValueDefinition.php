<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

use Omega\Container\ContainerInterface;
use function sprintf;
use function var_export;

/**
 * Definition of a value for dependency injection.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ValueDefinition implements DefinitionInterface, SelfResolvingDefinitionInterface
{
    /**
     * Entry name.
     */
    private string $name = '';

    public function __construct(
        private readonly mixed $value,
    ) {
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getValue() : mixed
    {
        return $this->value;
    }

    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function resolve(ContainerInterface $container) : mixed
    {
        return $this->getValue();
    }

    /**
     * @param ContainerInterface $container
     * @return bool
     */
    public function isResolvable(ContainerInterface $container) : bool
    {
        return true;
    }

    /**
     * @param callable $replacer
     * @return void
     */
    public function replaceNestedDefinitions(callable $replacer) : void
    {
        // no nested definitions
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return sprintf('Value (%s)', var_export($this->value, true));
    }
}
