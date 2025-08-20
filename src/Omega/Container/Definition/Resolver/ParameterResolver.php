<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Resolver;

use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\ObjectDefinition\MethodInjection;
use Omega\Container\Exceptions\DependencyException;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

use function array_key_exists;

/**
 * Resolves parameters for a function call.
 */
readonly class ParameterResolver
{
    /**
     * @param DefinitionResolverInterface $definitionResolver Will be used to resolve nested definitions.
     */
    public function __construct(
        private DefinitionResolverInterface $definitionResolver,
    ) {
    }

    /**
     * @return array Parameters to use to call the function.
     * @throws InvalidDefinitionException A parameter has no value defined or guessable.
     * @throws DependencyException
     */
    public function resolveParameters(
        ?MethodInjection $definition = null,
        ?ReflectionMethod $method = null,
        array $parameters = [],
    ) : array {
        $args = [];

        if (! $method) {
            return $args;
        }

        $definitionParameters = $definition ? $definition->parameters : [];

        foreach ($method->getParameters() as $index => $parameter) {
            if (array_key_exists($parameter->getName(), $parameters)) {
                // Look in the $parameters array
                $value = &$parameters[$parameter->getName()];
            } elseif (array_key_exists($index, $definitionParameters)) {
                // Look in the definition
                $value = &$definitionParameters[$index];
            } else {
                // If the parameter is optional and wasn't specified, we take its default value
                if ($parameter->isDefaultValueAvailable() || $parameter->isOptional()) {
                    $args[] = $this->getParameterDefaultValue($parameter, $method);
                    continue;
                }

                throw new InvalidDefinitionException(sprintf(
                    'Parameter $%s of %s has no value defined or guessable',
                    $parameter->getName(),
                    $this->getFunctionName($method)
                ));
            }

            // Nested definitions
            if ($value instanceof DefinitionInterface) {
                // If the container cannot produce the entry, we can use the default parameter value
                if ($parameter->isOptional() && ! $this->definitionResolver->isResolvable($value)) {
                    $value = $this->getParameterDefaultValue($parameter, $method);
                } else {
                    $value = $this->definitionResolver->resolve($value);
                }
            }

            $args[] = &$value;
        }

        return $args;
    }

    /**
     * Returns the default value of a function parameter.
     *
     * @throws InvalidDefinitionException Can't get default values from PHP internal classes and functions
     */
    private function getParameterDefaultValue(ReflectionParameter $parameter, ReflectionMethod $function) : mixed
    {
        try {
            return $parameter->getDefaultValue();
        } catch (ReflectionException) {
            throw new InvalidDefinitionException(sprintf(
                'The parameter "%s" of %s has no type defined or guessable. It has a default value, '
                . 'but the default value can\'t be read through Reflection because it is a PHP internal class.',
                $parameter->getName(),
                $this->getFunctionName($function)
            ));
        }
    }

    private function getFunctionName(ReflectionMethod $method) : string
    {
        return $method->getName() . '()';
    }
}
