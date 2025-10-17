<?php

declare(strict_types=1);

namespace Omega\Container\Definition\ObjectDefinition;

use Omega\Container\Definition\DefinitionInterface;

use function array_map;
use function sprintf;

/**
 * Describe an injection in an object method.
 */
class MethodInjection implements DefinitionInterface
{
    /**
     * @param string $methodName
     * @param array $parameters
     */
    public function __construct(
        public string $methodName {
        get {
        return $this->methodName;
        }
        },
        public array $parameters = [] {
        get {
        return $this->parameters;
        }
        },
    ) {
    }

    /**
     * @param array $parameters
     * @return self
     */
    public static function constructor(array $parameters = []): self
    {
        return new self('__construct', $parameters);
    }

    /**
     * Replace the parameters of the definition by a new array of parameters.
     *
     * @param array $parameters
     * @return void
     */
    public function replaceParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @param MethodInjection $definition
     * @return void
     */
    public function merge(self $definition): void
    {
        // In case of conflicts, the current definition prevails.
        $this->parameters += $definition->parameters;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return '';
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        // The name does not matter for method injections
    }

    /**
     * @param callable $replacer
     * @return void
     */
    public function replaceNestedDefinitions(callable $replacer): void
    {
        $this->parameters = array_map($replacer, $this->parameters);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('method(%s)', $this->methodName);
    }
}
