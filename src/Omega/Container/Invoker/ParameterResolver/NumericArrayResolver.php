<?php

declare(strict_types=1);

namespace Omega\Container\Invoker\ParameterResolver;

use ReflectionFunctionAbstract;

use function array_diff_key;
use function is_int;

/**
 * Simply returns all the values of the $providedParameters array that are
 * indexed by the parameter position (i.e. a number).
 *
 * E.g. `->call($callable, ['foo', 'bar'])` will simply resolve the parameters
 * to `['foo', 'bar']`.
 *
 * Parameters that are not indexed by a number (i.e. parameter position)
 * will be ignored.
 */
class NumericArrayResolver implements ParameterResolverInterface
{
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
        // Skip parameters already resolved
        if (! empty($resolvedParameters)) {
            $providedParameters = array_diff_key($providedParameters, $resolvedParameters);
        }

        foreach ($providedParameters as $key => $value) {
            if (is_int($key)) {
                $resolvedParameters[$key] = $value;
            }
        }

        return $resolvedParameters;
    }
}
