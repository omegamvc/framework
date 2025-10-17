<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Dumper;

use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\ObjectDefinition;
use Omega\Container\Definition\ObjectDefinition\MethodInjection;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

use function array_key_exists;
use function class_exists;
use function implode;
use function interface_exists;
use function sprintf;
use function var_export;

use const PHP_EOL;

/**
 * Dumps object definitions to string for debugging purposes.
 */
class ObjectDefinitionDumper
{
    /**
     * Returns the definition as string representation.
     *
     * @param ObjectDefinition $definition
     * @return string
     * @throws ReflectionException
     */
    public function dump(ObjectDefinition $definition): string
    {
        $className = $definition->getClassName();
        $classExist = class_exists($className) || interface_exists($className);

        // Class
        if (! $classExist) {
            $warning = '#UNKNOWN# ';
        } else {
            $class = new ReflectionClass($className);
            $warning = $class->isInstantiable() ? '' : '#NOT INSTANTIABLE# ';
        }
        $str = sprintf('    class = %s%s', $warning, $className);

        // Lazy
        $str .= PHP_EOL . '    lazy = ' . var_export($definition->isLazy(), true);

        if ($classExist) {
            // Constructor
            $str .= $this->dumpConstructor($className, $definition);

            // Properties
            $str .= $this->dumpProperties($definition);

            // Methods
            $str .= $this->dumpMethods($className, $definition);
        }

        return sprintf('Object (' . PHP_EOL . '%s' . PHP_EOL . ')', $str);
    }

    /**
     * @param class-string $className
     * @param ObjectDefinition $definition
     * @return string
     * @throws ReflectionException
     */
    private function dumpConstructor(string $className, ObjectDefinition $definition): string
    {
        $str = '';

        $constructorInjection = $definition->getConstructorInjection();

        if ($constructorInjection !== null) {
            $parameters = $this->dumpMethodParameters($className, $constructorInjection);

            $str .= sprintf(
                PHP_EOL
                . '    __construct('
                . PHP_EOL
                . '        %s'
                . PHP_EOL . '    )',
                $parameters
            );
        }

        return $str;
    }

    /**
     * @param ObjectDefinition $definition
     * @return string
     */
    private function dumpProperties(ObjectDefinition $definition): string
    {
        $str = '';

        foreach ($definition->propertyInjections as $propertyInjection) {
            $value = $propertyInjection->value;
            $valueStr = $value instanceof DefinitionInterface ? (string) $value : var_export($value, true);

            $str .= sprintf(PHP_EOL . '    $%s = %s', $propertyInjection->propertyName, $valueStr);
        }

        return $str;
    }

    /**
     * @param class-string $className
     * @param ObjectDefinition $definition
     * @return string
     * @throws ReflectionException
     */
    private function dumpMethods(string $className, ObjectDefinition $definition): string
    {
        $str = '';

        foreach ($definition->getMethodInjections() as $methodInjection) {
            $parameters = $this->dumpMethodParameters($className, $methodInjection);

            $str .= sprintf(
                PHP_EOL
                . '    %s('
                . PHP_EOL
                . '        %s'
                . PHP_EOL
                . '    )',
                $methodInjection->methodName,
                $parameters
            );
        }

        return $str;
    }

    /**
     * @param class-string $className
     * @param MethodInjection $methodInjection
     * @return string
     * @throws ReflectionException
     */
    private function dumpMethodParameters(string $className, MethodInjection $methodInjection): string
    {
        $methodReflection = new ReflectionMethod($className, $methodInjection->methodName);

        $args = [];

        $definitionParameters = $methodInjection->parameters;

        foreach ($methodReflection->getParameters() as $index => $parameter) {
            if (array_key_exists($index, $definitionParameters)) {
                $value = $definitionParameters[$index];
                $valueStr = $value instanceof DefinitionInterface ? (string) $value : var_export($value, true);

                $args[] = sprintf('$%s = %s', $parameter->getName(), $valueStr);

                continue;
            }

            // If the parameter is optional and wasn't specified, we take its default value
            if ($parameter->isOptional()) {
                try {
                    $value = $parameter->getDefaultValue();

                    $args[] = sprintf(
                        '$%s = (default value) %s',
                        $parameter->getName(),
                        var_export($value, true)
                    );
                    continue;
                } catch (ReflectionException) {
                    // The default value can't be read through Reflection because it is a PHP internal class
                }
            }

            $args[] = sprintf('$%s = #UNDEFINED#', $parameter->getName());
        }

        return implode(PHP_EOL . '        ', $args);
    }
}
