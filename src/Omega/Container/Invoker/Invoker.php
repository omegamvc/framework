<?php declare(strict_types=1);

namespace Omega\Container\Invoker;

use Omega\Container\ContainerInterface;
use Omega\Container\Exceptions\ContainerExceptionInterface;
use Omega\Container\Exceptions\NotFoundExceptionInterface;
use Omega\Container\Invoker\Exception\NotCallableException;
use Omega\Container\Invoker\Exception\NotEnoughParametersException;
use Omega\Container\Invoker\ParameterResolver\AssociativeArrayResolver;
use Omega\Container\Invoker\ParameterResolver\DefaultValueResolver;
use Omega\Container\Invoker\ParameterResolver\NumericArrayResolver;
use Omega\Container\Invoker\ParameterResolver\ParameterResolverInterface;
use Omega\Container\Invoker\ParameterResolver\ResolverChain;
use Omega\Container\Invoker\Reflection\CallableReflection;
use ReflectionException;
use ReflectionParameter;

use function array_diff_key;
use function assert;
use function call_user_func_array;
use function get_class;
use function is_callable;
use function is_object;
use function ksort;
use function reset;
use function var_export;

/**
 * Invoke a callable.
 */
class Invoker implements InvokerInterface
{
    /** @var CallableResolver|null */
    private ?CallableResolver $callableResolver {
        get {
            return $this->callableResolver;
        }
    }

    /** @var ParameterResolverInterface */
    private ParameterResolverInterface $parameterResolver {
        get {
            return $this->parameterResolver;
        }
    }

    /** @var ContainerInterface|null */
    private ?ContainerInterface $container {
        get {
            return $this->container;
        }
    }

    /**
     * @param ParameterResolverInterface|null $parameterResolver
     * @param ContainerInterface|null $container
     */
    public function __construct(?ParameterResolverInterface $parameterResolver = null, ?ContainerInterface $container = null)
    {
        $this->parameterResolver = $parameterResolver ?: $this->createParameterResolver();
        $this->container = $container;

        if ($container) {
            $this->callableResolver = new CallableResolver($container);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotCallableException
     * @throws NotEnoughParametersException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function call($callable, array $parameters = []): mixed
    {
        if ($this->callableResolver) {
            $callable = $this->callableResolver->resolve($callable);
        }

        if (!is_callable($callable)) {
            throw new NotCallableException(sprintf(
                '%s is not a callable',
                is_object($callable) ? 'Instance of ' . get_class($callable) : var_export($callable, true)
            ));
        }

        $callableReflection = CallableReflection::create($callable);

        $args = $this->parameterResolver->getParameters($callableReflection, $parameters, []);

        // Sort by array key because call_user_func_array ignores numeric keys
        ksort($args);

        // Check all parameters are resolved
        $diff = array_diff_key($callableReflection->getParameters(), $args);
        $parameter = reset($diff);
        if ($parameter && assert($parameter instanceof ReflectionParameter) && ! $parameter->isVariadic()) {
            throw new NotEnoughParametersException(sprintf(
                'Unable to invoke the callable because no value was given for parameter %d ($%s)',
                $parameter->getPosition() + 1,
                $parameter->name
            ));
        }

        return call_user_func_array($callable, $args);
    }

    /**
     * Create the default parameter resolver.
     *
     * @return ParameterResolverInterface
     */
    private function createParameterResolver(): ParameterResolverInterface
    {
        return new ResolverChain([
            new NumericArrayResolver,
            new AssociativeArrayResolver,
            new DefaultValueResolver,
        ]);
    }
}
