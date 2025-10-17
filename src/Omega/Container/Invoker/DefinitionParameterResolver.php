<?php

declare(strict_types=1);

namespace Omega\Container\Invoker;

use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\Helper\DefinitionHelperInterface;
use Omega\Container\Definition\Resolver\DefinitionResolverInterface;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Invoker\ParameterResolver\ParameterResolverInterface;
use ReflectionFunctionAbstract;

use function array_diff_key;
use function is_int;

/**
 * Resolves callable parameters using definitions.
 */
readonly class DefinitionParameterResolver implements ParameterResolverInterface
{
    /**
     * @param DefinitionResolverInterface $definitionResolver
     */
    public function __construct(
        private DefinitionResolverInterface $definitionResolver,
    ) {
    }

    /**
     * @param ReflectionFunctionAbstract $reflection
     * @param array $providedParameters
     * @param array $resolvedParameters
     * @return array
     * @throws InvalidDefinitionException
     * @throws DependencyException
     */
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters,
    ): array {
        // Skip parameters already resolved
        if (! empty($resolvedParameters)) {
            $providedParameters = array_diff_key($providedParameters, $resolvedParameters);
        }

        foreach ($providedParameters as $key => $value) {
            if ($value instanceof DefinitionHelperInterface) {
                $value = $value->getDefinition('');
            }

            if (! $value instanceof DefinitionInterface) {
                continue;
            }

            $value = $this->definitionResolver->resolve($value);

            if (is_int($key)) {
                // Indexed by position
                $resolvedParameters[$key] = $value;
            } else {
                // Indexed by parameter name
                // TODO optimize?
                $reflectionParameters = $reflection->getParameters();
                foreach ($reflectionParameters as $reflectionParameter) {
                    if ($key === $reflectionParameter->name) {
                        $resolvedParameters[$reflectionParameter->getPosition()] = $value;
                    }
                }
            }
        }

        return $resolvedParameters;
    }
}
