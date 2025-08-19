<?php

declare(strict_types=1);

namespace Omega\Container\Compiler;

use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\ObjectDefinition;
use Omega\Container\Definition\ObjectDefinition\MethodInjection;
use Omega\Container\Exceptions\DependencyException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

use function array_key_exists;
use function array_map;
use function implode;
use function sprintf;
use function str_contains;
use function var_export;

use const PHP_VERSION_ID;

/**
 * Compiles an object definition into native PHP code that, when executed, creates the object.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ObjectCreationCompiler
{
    public function __construct(
        private Compiler $compiler,
    ) {
    }

    /**
     * @param ObjectDefinition $definition
     * @return string
     * @throws InvalidDefinitionException
     * @throws DependencyException
     * @throws ReflectionException
     */
    public function compile(ObjectDefinition $definition) : string
    {
        $this->assertClassIsNotAnonymous($definition);
        $this->assertClassIsInstantiable($definition);
        /** @var class-string $className At this point we have checked the class is valid */
        $className = $definition->getClassName();

        // Lazy?
        if ($definition->isLazy()) {
            return $this->compileLazyDefinition($definition);
        }

        try {
            $classReflection = new ReflectionClass($className);
            $constructorArguments = $this->resolveParameters(
                $definition->getConstructorInjection(), $classReflection->getConstructor()
            );
            $dumpedConstructorArguments = array_map(function ($value) {
                return $this->compiler->compileValue($value);
            }, $constructorArguments);

            $code   = [];
            $code[] = sprintf(
                '$object = new %s(%s);',
                $className,
                implode(', ', $dumpedConstructorArguments)
            );

            // Property injections
            foreach ($definition->getPropertyInjections() as $propertyInjection) {
                $value = $propertyInjection->value;
                $value = $this->compiler->compileValue($value);

                $propertyClassName = $propertyInjection->className ?: $className;
                $property = new ReflectionProperty($propertyClassName, $propertyInjection->propertyName);
                if ($property->isPublic() && !(PHP_VERSION_ID >= 80100 && $property->isReadOnly())) {
                    $code[] = sprintf('$object->%s = %s;', $propertyInjection->propertyName, $value);
                } else {
                    // Private/protected/readonly property
                    $code[] = sprintf(
                        '\Omega\Container\Definition\Resolver\ObjectCreator::setPrivatePropertyValue(%s, $object, \'%s\', %s);',
                        var_export($propertyInjection->className, true),
                        $propertyInjection->propertyName,
                        $value
                    );
                }
            }

            // Method injections
            foreach ($definition->getMethodInjections() as $methodInjection) {
                $methodReflection = new ReflectionMethod($className, $methodInjection->methodName);
                $parameters = $this->resolveParameters($methodInjection, $methodReflection);

                $dumpedParameters = array_map(function ($value) {
                    return $this->compiler->compileValue($value);
                }, $parameters);

                $code[] = sprintf(
                    '$object->%s(%s);',
                    $methodInjection->methodName,
                    implode(', ', $dumpedParameters)
                );
            }
        } catch (InvalidDefinitionException $e) {
            throw InvalidDefinitionException::create($definition, sprintf(
                'Entry "%s" cannot be compiled: %s',
                $definition->getName(),
                $e->getMessage()
            ));
        }

        return implode("\n        ", $code);
    }

    /**
     * @param MethodInjection|null $definition
     * @param ReflectionMethod|null $method
     * @return array
     * @throws InvalidDefinitionException
     */
    public function resolveParameters(?MethodInjection $definition, ?ReflectionMethod $method) : array
    {
        $args = [];

        if (! $method) {
            return $args;
        }

        $definitionParameters = $definition ? $definition->parameters : [];

        foreach ($method->getParameters() as $index => $parameter) {
            if (array_key_exists($index, $definitionParameters)) {
                // Look in the definition
                $value = &$definitionParameters[$index];
            } elseif ($parameter->isOptional()) {
                // If the parameter is optional and wasn't specified, we take its default value
                $args[] = $this->getParameterDefaultValue($parameter, $method);
                continue;
            } else {
                throw new InvalidDefinitionException(sprintf(
                    'Parameter $%s of %s has no value defined or guessable',
                    $parameter->getName(),
                    $this->getFunctionName($method)
                ));
            }

            $args[] = &$value;
        }

        return $args;
    }

    /**
     * @param ObjectDefinition $definition
     * @return string
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws ReflectionException
     */
    private function compileLazyDefinition(ObjectDefinition $definition) : string
    {
        $subDefinition = clone $definition;
        $subDefinition->setLazy(false);
        $subDefinition = $this->compiler->compileValue($subDefinition);

        /** @var class-string $className At this point we have checked the class is valid */
        $className = $definition->getClassName();

        $this->compiler->getProxyFactory()->generateProxyClass($className);

        return <<<STR
                    \$object = \$this->proxyFactory->createProxy(
                        '{$definition->getClassName()}',
                        function () {
                            return $subDefinition;
                        }
                    );
            STR;
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

    /**
     * @param ReflectionMethod $method
     * @return string
     */
    private function getFunctionName(ReflectionMethod $method) : string
    {
        return $method->getName() . '()';
    }

    /**
     * @param ObjectDefinition $definition
     * @return void
     * @throws InvalidDefinitionException
     */
    private function assertClassIsNotAnonymous(ObjectDefinition $definition) : void
    {
        if (str_contains($definition->getClassName(), '@')) {
            throw InvalidDefinitionException::create($definition, sprintf(
                'Entry "%s" cannot be compiled: anonymous classes cannot be compiled',
                $definition->getName()
            ));
        }
    }

    /**
     * @param ObjectDefinition $definition
     * @return void
     * @throws InvalidDefinitionException
     */
    private function assertClassIsInstantiable(ObjectDefinition $definition) : void
    {
        if ($definition->isInstantiable()) {
            return;
        }

        $message = ! $definition->classExists()
            ? 'Entry "%s" cannot be compiled: the class does\'t exist'
            : 'Entry "%s" cannot be compiled: the class is not instantiable';

        throw InvalidDefinitionException::create($definition, sprintf($message, $definition->getName()));
    }
}
