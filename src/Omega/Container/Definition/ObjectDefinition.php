<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

use Omega\Container\Definition\Dumper\ObjectDefinitionDumper;
use Omega\Container\Definition\ObjectDefinition\MethodInjection;
use Omega\Container\Definition\ObjectDefinition\PropertyInjection;
use Omega\Container\Definition\Source\DefinitionArray;
use ReflectionClass;
use ReflectionException;

use function array_walk;
use function array_walk_recursive;
use function class_exists;
use function interface_exists;
use function strpos;
use function substr_replace;

/**
 * Defines how an object can be instantiated.
 */
class ObjectDefinition implements DefinitionInterface
{
    /** @var string Entry name (most of the time, same as $classname). */
    private string $name;

    /** @var string|null Class name (if null, then the class name is $name). */
    protected ?string $className = null;

    /** @var MethodInjection|null  */
    protected ?MethodInjection $constructorInjection = null;

    /** @var array  */
    public array $propertyInjections = [];

    /** @var MethodInjection[][] Method calls. */
    protected array $methodInjections = [];

    /** @var bool|null  */
    protected ?bool $lazy = null;

    /** @var bool Store if the class exists. Storing it (in cache) avoids recomputing this. */
    private bool $classExists;

    /** @var bool Store if the class is instantiable. Storing it (in cache) avoids recomputing this. */
    public bool $isInstantiable;

    /**
     * @param string $name Entry name
     * @param string|null $className
     */
    public function __construct(string $name, ?string $className = null)
    {
        $this->name = $name;
        $this->setClassName($className);
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * @param string|null $className
     * @return void
     */
    public function setClassName(?string $className) : void
    {
        $this->className = $className;

        $this->updateCache();
    }

    /**
     * @return string
     */
    public function getClassName() : string
    {
        return $this->className ?? $this->name;
    }

    /**
     * @return MethodInjection|null
     */
    public function getConstructorInjection() : ?MethodInjection
    {
        return $this->constructorInjection;
    }

    /**
     * @param MethodInjection $constructorInjection
     * @return void
     */
    public function setConstructorInjection(MethodInjection $constructorInjection) : void
    {
        $this->constructorInjection = $constructorInjection;
    }

    /**
     * @param MethodInjection $injection
     * @return void
     */
    public function completeConstructorInjection(MethodInjection $injection) : void
    {
        if ($this->constructorInjection !== null) {
            // Merge
            $this->constructorInjection->merge($injection);
        } else {
            // Set
            $this->constructorInjection = $injection;
        }
    }

    /**
     * @param PropertyInjection $propertyInjection
     * @return void
     */
    public function addPropertyInjection(PropertyInjection $propertyInjection) : void
    {
        $className = $propertyInjection->className;
        if ($className) {
            // Index with the class name to avoid collisions between parent and
            // child private properties with the same name
            $key = $className . '::' . $propertyInjection->propertyName;
        } else {
            $key = $propertyInjection->propertyName;
        }

        $this->propertyInjections[$key] = $propertyInjection;
    }

    /**
     * @return MethodInjection[] Method injections
     */
    public function getMethodInjections() : array
    {
        // Return array leafs
        $injections = [];
        array_walk_recursive($this->methodInjections, function ($injection) use (&$injections) {
            $injections[] = $injection;
        });

        return $injections;
    }

    /**
     * @param MethodInjection $methodInjection
     * @return void
     */
    public function addMethodInjection(MethodInjection $methodInjection) : void
    {
        $method = $methodInjection->methodName;
        if (! isset($this->methodInjections[$method])) {
            $this->methodInjections[$method] = [];
        }
        $this->methodInjections[$method][] = $methodInjection;
    }

    /**
     * @param MethodInjection $injection
     * @return void
     */
    public function completeFirstMethodInjection(MethodInjection $injection) : void
    {
        $method = $injection->methodName;

        if (isset($this->methodInjections[$method][0])) {
            // Merge
            $this->methodInjections[$method][0]->merge($injection);
        } else {
            // Set
            $this->addMethodInjection($injection);
        }
    }

    /**
     * @param bool|null $lazy
     * @return void
     */
    public function setLazy(?bool $lazy = null) : void
    {
        $this->lazy = $lazy;
    }

    /**
     * @return bool
     */
    public function isLazy() : bool
    {
        if ($this->lazy !== null) {
            return $this->lazy;
        }

        // Default value
        return false;
    }

    /**
     * @return bool
     */
    public function classExists() : bool
    {
        return $this->classExists;
    }

    /**
     * @param callable $replacer
     * @return void
     */
    public function replaceNestedDefinitions(callable $replacer) : void
    {
        array_walk($this->propertyInjections, function (PropertyInjection $propertyInjection) use ($replacer) {
            $propertyInjection->replaceNestedDefinition($replacer);
        });

        $this->constructorInjection?->replaceNestedDefinitions($replacer);

        array_walk($this->methodInjections, function ($injectionArray) use ($replacer) {
            array_walk($injectionArray, function (MethodInjection $methodInjection) use ($replacer) {
                $methodInjection->replaceNestedDefinitions($replacer);
            });
        });
    }

    /**
     * Replaces all the wildcards in the string with the given replacements.
     *
     * @param string[] $replacements
     * @return void
     */
    public function replaceWildcards(array $replacements) : void
    {
        $className = $this->getClassName();

        foreach ($replacements as $replacement) {
            $pos = strpos($className, DefinitionArray::WILDCARD);
            if ($pos !== false) {
                $className = substr_replace($className, $replacement, $pos, 1);
            }
        }

        $this->setClassName($className);
    }

    /**
     * @return string
     * @throws ReflectionException
     */
    public function __toString() : string
    {
        return (new ObjectDefinitionDumper)->dump($this);
    }

    /**
     * @return void
     */
    private function updateCache() : void
    {
        $className = $this->getClassName();

        $this->classExists = class_exists($className) || interface_exists($className);

        if (! $this->classExists) {
            $this->isInstantiable = false;

            return;
        }

        /** @var class-string $className */
        $class = new ReflectionClass($className);
        $this->isInstantiable = $class->isInstantiable();
    }
}
