<?php declare(strict_types=1);

namespace Omega\Container\Invoker\ParameterResolver;

use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use function array_diff_key;
use function assert;

/**
 * Finds the default value for a parameter, *if it exists*.
 */
class DefaultValueResolver implements ParameterResolverInterface
{
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
            assert($parameter instanceof ReflectionParameter);
            if ($parameter->isDefaultValueAvailable()) {
                try {
                    $resolvedParameters[$index] = $parameter->getDefaultValue();
                } catch (ReflectionException $e) {
                    // Can't get default values from PHP internal classes and functions
                }
            } else {
                $parameterType = $parameter->getType();
                if ($parameterType && $parameterType->allowsNull()) {
                    $resolvedParameters[$index] = null;
                }
            }
        }

        return $resolvedParameters;
    }
}
