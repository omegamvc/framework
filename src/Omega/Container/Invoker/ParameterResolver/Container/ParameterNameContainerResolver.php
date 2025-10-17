<?php

declare(strict_types=1);

namespace Omega\Container\Invoker\ParameterResolver\Container;

use Omega\Container\ContainerInterface;
use Omega\Container\Exceptions\ContainerExceptionInterface;
use Omega\Container\Exceptions\NotFoundExceptionInterface;
use Omega\Container\Invoker\ParameterResolver\ParameterResolverInterface;
use ReflectionFunctionAbstract;

use function array_diff_key;

/**
 * Inject entries from a DI container using the parameter names.
 */
class ParameterNameContainerResolver implements ParameterResolverInterface
{
    /** @var ContainerInterface */
    private ContainerInterface $container;

    /**
     * @param ContainerInterface $container The container to get entries from.
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
        array $resolvedParameters
    ): array {
        $parameters = $reflection->getParameters();

        // Skip parameters already resolved
        if (! empty($resolvedParameters)) {
            $parameters = array_diff_key($parameters, $resolvedParameters);
        }

        foreach ($parameters as $index => $parameter) {
            $name = $parameter->name;

            if ($name && $this->container->has($name)) {
                $resolvedParameters[$index] = $this->container->get($name);
            }
        }

        return $resolvedParameters;
    }
}
