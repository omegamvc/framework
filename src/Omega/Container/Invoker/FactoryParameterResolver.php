<?php

declare(strict_types=1);

namespace Omega\Container\Invoker;

use Omega\Container\ContainerInterface;
use Omega\Container\Exceptions\ContainerExceptionInterface;
use Omega\Container\Exceptions\NotFoundExceptionInterface;
use Omega\Container\Invoker\ParameterResolver\ParameterResolverInterface;
use ReflectionFunctionAbstract;
use ReflectionNamedType;
use function array_diff_key;

/**
 * Inject the container, the definition or any other service using type-hints.
 */
readonly class FactoryParameterResolver implements ParameterResolverInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    /**
     * @param ReflectionFunctionAbstract $reflection
     * @param array $providedParameters
     * @param array $resolvedParameters
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters,
    ) : array {
        $parameters = $reflection->getParameters();

        // Skip parameters already resolved
        if (! empty($resolvedParameters)) {
            $parameters = array_diff_key($parameters, $resolvedParameters);
        }

        foreach ($parameters as $index => $parameter) {
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

            $parameterClass = $parameterType->getName();

            if ($parameterClass === 'Omega\Container\ContainerInterface') {
                $resolvedParameters[$index] = $this->container;
            } elseif ($parameterClass === 'Omega\Container\Factory\RequestedEntry') {
                // By convention the second parameter is the definition
                $resolvedParameters[$index] = $providedParameters[1];
            } elseif ($this->container->has($parameterClass)) {
                $resolvedParameters[$index] = $this->container->get($parameterClass);
            }
        }

        return $resolvedParameters;
    }
}
