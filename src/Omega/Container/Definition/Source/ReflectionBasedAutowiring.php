<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use Omega\Container\Definition\ObjectDefinition;
use Omega\Container\Definition\ObjectDefinition\MethodInjection;
use Omega\Container\Definition\Reference;
use ReflectionNamedType;

/**
 * Reads DI class definitions using reflection.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ReflectionBasedAutowiring implements DefinitionSourceInterface, AutowiringInterface
{
    public function autowire(string $name, ?ObjectDefinition $definition = null): ?ObjectDefinition
    {
        $className = $definition ? $definition->getClassName() : $name;

        if (!class_exists($className) && !interface_exists($className)) {
            return $definition;
        }

        $definition = $definition ?: new ObjectDefinition($name);

        // Constructor
        $class = new \ReflectionClass($className);
        $constructor = $class->getConstructor();
        if ($constructor && $constructor->isPublic()) {
            $constructorInjection = MethodInjection::constructor($this->getParametersDefinition($constructor));
            $definition->completeConstructorInjection($constructorInjection);
        }

        return $definition;
    }

    public function getDefinition(string $name): ?ObjectDefinition
    {
        return $this->autowire($name);
    }

    /**
     * Autowiring cannot guess all existing definitions.
     */
    public function getDefinitions(): array
    {
        return [];
    }

    /**
     * Read the type-hinting from the parameters of the function.
     */
    private function getParametersDefinition(\ReflectionFunctionAbstract $constructor): array
    {
        $parameters = [];

        foreach ($constructor->getParameters() as $index => $parameter) {
            // Skip optional parameters
            if ($parameter->isOptional()) {
                continue;
            }

            $parameterType = $parameter->getType();
            if (!$parameterType) {
                // No type
                continue;
            }
            if (!$parameterType instanceof ReflectionNamedType) {
                // Union types are not supported
                continue;
            }
            if ($parameterType->isBuiltin()) {
                // Primitive types are not supported
                continue;
            }

            $parameters[$index] = new Reference($parameterType->getName());
        }

        return $parameters;
    }
}
