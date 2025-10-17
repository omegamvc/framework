<?php

declare(strict_types=1);

namespace Omega\Container\Invoker\ParameterResolver;

use ReflectionFunctionAbstract;

use function array_diff_key;
use function array_unshift;

/**
 * Dispatches the call to other resolvers until all parameters are resolved.
 *
 * Chain of responsibility pattern.
 */
class ResolverChain implements ParameterResolverInterface
{
    /** @var ParameterResolverInterface[] */
    private array $resolvers;

    /**
     * @param array $resolvers
     */
    public function __construct(array $resolvers = [])
    {
        $this->resolvers = $resolvers;
    }

    /**
     * @param ReflectionFunctionAbstract $reflection
     * @param array $providedParameters
     * @param array $resolvedParameters
     * @return array
     */
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {
        $reflectionParameters = $reflection->getParameters();

        foreach ($this->resolvers as $resolver) {
            $resolvedParameters = $resolver->getParameters(
                $reflection,
                $providedParameters,
                $resolvedParameters
            );

            $diff = array_diff_key($reflectionParameters, $resolvedParameters);
            if (empty($diff)) {
                // Stop traversing: all parameters are resolved
                return $resolvedParameters;
            }
        }

        return $resolvedParameters;
    }

    /**
     * Push a parameter resolver after the ones already registered.
     *
     * @param ParameterResolverInterface $resolver
     * @return void
     */
    public function appendResolver(ParameterResolverInterface $resolver): void
    {
        $this->resolvers[] = $resolver;
    }

    /**
     * Insert a parameter resolver before the ones already registered.
     *
     * @param ParameterResolverInterface $resolver
     * @return void
     */
    public function prependResolver(ParameterResolverInterface $resolver): void
    {
        array_unshift($this->resolvers, $resolver);
    }
}
