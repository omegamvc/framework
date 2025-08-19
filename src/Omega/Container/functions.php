<?php

declare(strict_types=1);

namespace Omega\Container;

use Omega\Container\Definition\ArrayDefinitionExtension;
use Omega\Container\Definition\EnvironmentVariableDefinition;
use Omega\Container\Definition\Helper\AutowireDefinitionHelper;
use Omega\Container\Definition\Helper\CreateDefinitionHelper;
use Omega\Container\Definition\Helper\FactoryDefinitionHelper;
use Omega\Container\Definition\Reference;
use Omega\Container\Definition\StringDefinition;
use Omega\Container\Definition\ValueDefinition;
use function func_num_args;
use function is_array;

if (! function_exists('Omega\Container\value')) {
    /**
     * Helper for defining a value.
     */
    function value(mixed $value) : ValueDefinition
    {
        return new ValueDefinition($value);
    }
}

if (! function_exists('Omega\Container\create')) {
    /**
     * Helper for defining an object.
     *
     * @param string|null $class_name Class name of the object.
     *                               If null, the name of the entry (in the container) will be used as class name.
     * @return CreateDefinitionHelper
     */
    function create(?string $class_name = null) : CreateDefinitionHelper
    {
        return new CreateDefinitionHelper($class_name);
    }
}

if (! function_exists('Omega\Container\autowire')) {
    /**
     * Helper for autowiring an object.
     *
     * @param string|null $class_name Class name of the object.
     *                               If null, the name of the entry (in the container) will be used as class name.
     * @return AutowireDefinitionHelper
     */
    function autowire(?string $class_name = null) : AutowireDefinitionHelper
    {
        return new AutowireDefinitionHelper($class_name);
    }
}

if (! function_exists('Omega\Container\factory')) {
    /**
     * Helper for defining a container entry using a factory function/callable.
     *
     * @param callable|array|string $factory The factory is a callable that takes the container as parameter
     *                                       and returns the value to register in the container.
     * @return FactoryDefinitionHelper
     */
    function factory(callable|array|string $factory) : FactoryDefinitionHelper
    {
        return new FactoryDefinitionHelper($factory);
    }
}

if (! function_exists('Omega\Container\decorate')) {
    /**
     * Decorate the previous definition using a callable.
     *
     * Example:
     *
     *     'foo' => decorate(function ($foo, $container) {
     *         return new CachedFoo($foo, $container->get('cache'));
     *     })
     *
     * @param callable|array| string $callable The callable takes the decorated object as first parameter and
     *                                         the container as second.
     * @return FactoryDefinitionHelper
     */
    function decorate(callable|array|string $callable) : FactoryDefinitionHelper
    {
        return new FactoryDefinitionHelper($callable, true);
    }
}

if (! function_exists('Omega\Container\get')) {
    /**
     * Helper for referencing another container entry in an object definition.
     *
     * @param string $entry_name
     * @return Reference
     */
    function get(string $entry_name) : Reference
    {
        return new Reference($entry_name);
    }
}

if (! function_exists('Omega\Container\env')) {
    /**
     * Helper for referencing environment variables.
     *
     * @param string $variableName The name of the environment variable.
     * @param mixed  $defaultValue The default value to be used if the environment variable is not defined.
     * @return EnvironmentVariableDefinition
     */
    function env(string $variableName, mixed $defaultValue = null) : EnvironmentVariableDefinition
    {
        // Only mark as optional if the default value was *explicitly* provided.
        $isOptional = 2 === func_num_args();

        return new EnvironmentVariableDefinition($variableName, $isOptional, $defaultValue);
    }
}

if (! function_exists('Omega\Container\add')) {
    /**
     * Helper for extending another definition.
     *
     * Example:
     *
     *     'log.backends' => DI\add(DI\get('My\Custom\LogBackend'))
     *
     * or:
     *
     *     'log.backends' => DI\add([
     *         DI\get('My\Custom\LogBackend')
     *     ])
     *
     * @param mixed|array $values A value or an array of values to add to the array.
     * @return ArrayDefinitionExtension
     */
    function add(mixed $values) : ArrayDefinitionExtension
    {
        if (!is_array($values)) {
            $values = [$values];
        }

        return new ArrayDefinitionExtension($values);
    }
}

if (! function_exists('Omega\Container\string')) {
    /**
     * Helper for concatenating strings.
     *
     * Example:
     *
     *     'log.filename' => Omega\Container\string('{app.path}/app.log')
     *
     * @param string $expression A string expression. Use the `{}` placeholders to reference other container entries.
     * @return StringDefinition
     */
    function string(string $expression) : StringDefinition
    {
        return new StringDefinition($expression);
    }
}
