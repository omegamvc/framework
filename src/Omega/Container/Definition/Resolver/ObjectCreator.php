<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Resolver;

use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\ObjectDefinition;
use Omega\Container\Definition\ObjectDefinition\PropertyInjection;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Proxy\ProxyFactoryInterface;
use Exception;
use Omega\Container\Exceptions\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Create objects based on an object definition.
 *
 * @template-implements DefinitionResolverInterface<ObjectDefinition>

 */
class ObjectCreator implements DefinitionResolverInterface
{
    private ParameterResolver $parameterResolver;

    /**
     * @param DefinitionResolverInterface $definitionResolver Used to resolve nested definitions.
     * @param ProxyFactoryInterface       $proxyFactory       Used to create proxies for lazy injections.
     */
    public function __construct(
        private readonly DefinitionResolverInterface $definitionResolver,
        private readonly ProxyFactoryInterface $proxyFactory,
    ) {
        $this->parameterResolver = new ParameterResolver($definitionResolver);
    }

    /**
     * Resolve a class definition to a value.
     *
     * This will create a new instance of the class using the injections points defined.
     *
     * @param DefinitionInterface $definition
     * @param array $parameters
     * @return object|null
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws ReflectionException
     */
    public function resolve(DefinitionInterface $definition, array $parameters = []): ?object
    {
        // Lazy?
        if ($definition->isLazy()) {
            return $this->createProxy($definition, $parameters);
        }

        return $this->createInstance($definition, $parameters);
    }

    /**
     * The definition is not resolvable if the class is not instantiable (interface or abstract)
     * or if the class doesn't exist.
     *
     * @param DefinitionInterface $definition
     * @param array $parameters
     * @return bool
     */
    public function isResolvable(DefinitionInterface $definition, array $parameters = []): bool
    {
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        return $definition->isInstantiable;
    }

    /**
     * Returns a proxy instance.
     */
    private function createProxy(ObjectDefinition $definition, array $parameters): object
    {
        /** @var class-string $className */
        $className = $definition->getClassName();

        return $this->proxyFactory->createProxy(
            $className,
            function () use ($definition, $parameters) {
                return $this->createInstance($definition, $parameters);
            }
        );
    }

    /**
     * Creates an instance of the class and injects dependencies.
     *
     * @param array $parameters Optional parameters to use to create the instance.
     *
     * @throws DependencyException
     * @throws InvalidDefinitionException
     * @throws ReflectionException;
     */
    private function createInstance(ObjectDefinition $definition, array $parameters): object
    {
        // Check that the class is instantiable
        if (!$definition->isInstantiable) {
            // Check that the class exists
            if (! $definition->classExists()) {
                throw InvalidDefinitionException::create($definition, sprintf(
                    'Entry "%s" cannot be resolved: the class does\'t exist',
                    $definition->getName()
                ));
            }

            throw InvalidDefinitionException::create($definition, sprintf(
                'Entry "%s" cannot be resolved: the class is not instantiable',
                $definition->getName()
            ));
        }

        $classname = $definition->getClassName();
        $classReflection = new ReflectionClass($classname);

        $constructorInjection = $definition->getConstructorInjection();

        try {
            $args = $this->parameterResolver->resolveParameters(
                $constructorInjection,
                $classReflection->getConstructor(),
                $parameters
            );

            $object = new $classname(...$args);

            $this->injectMethodsAndProperties($object, $definition);
        } catch (NotFoundExceptionInterface $e) {
            throw new DependencyException(sprintf(
                'Error while injecting dependencies into %s: %s',
                $classReflection->getName(),
                $e->getMessage()
            ), 0, $e);
        } catch (InvalidDefinitionException $e) {
            throw InvalidDefinitionException::create($definition, sprintf(
                'Entry "%s" cannot be resolved: %s',
                $definition->getName(),
                $e->getMessage()
            ));
        }

        return $object;
    }

    /**
     * @param object $object
     * @param ObjectDefinition $objectDefinition
     * @return void
     * @throws InvalidDefinitionException
     * @throws ReflectionException
     * @throws DependencyException
     */
    protected function injectMethodsAndProperties(object $object, ObjectDefinition $objectDefinition): void
    {
        // Property injections
        foreach ($objectDefinition->propertyInjections as $propertyInjection) {
            $this->injectProperty($object, $propertyInjection);
        }

        // Method injections
        foreach ($objectDefinition->getMethodInjections() as $methodInjection) {
            $methodReflection = new ReflectionMethod($object, $methodInjection->methodName);
            $args = $this->parameterResolver->resolveParameters($methodInjection, $methodReflection);

            $methodReflection->invokeArgs($object, $args);
        }
    }

    /**
     * Inject dependencies into properties.
     *
     * @param object            $object            Object to inject dependencies into
     * @param PropertyInjection $propertyInjection Property injection definition
     * @return void
     * @throws DependencyException
     * @throws ReflectionException
     */
    private function injectProperty(object $object, PropertyInjection $propertyInjection): void
    {
        $propertyName = $propertyInjection->propertyName;

        $value = $propertyInjection->value;

        if ($value instanceof DefinitionInterface) {
            try {
                $value = $this->definitionResolver->resolve($value);
            } catch (DependencyException $e) {
                throw $e;
            } catch (Exception $e) {
                throw new DependencyException(sprintf(
                    'Error while injecting in %s::%s. %s',
                    $object::class,
                    $propertyName,
                    $e->getMessage()
                ), 0, $e);
            }
        }

        self::setPrivatePropertyValue($propertyInjection->className, $object, $propertyName, $value);
    }

    /**
     * @param string|null $className
     * @param $object
     * @param string $propertyName
     * @param mixed $propertyValue
     * @return void
     * @throws ReflectionException
     */
    public static function setPrivatePropertyValue(
        ?string $className,
        $object,
        string $propertyName,
        mixed $propertyValue
    ): void {
        $className = $className ?: $object::class;
        $property = new ReflectionProperty($className, $propertyName);
        $property->setValue($object, $propertyValue);
    }
}
