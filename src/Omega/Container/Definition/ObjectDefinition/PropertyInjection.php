<?php

declare(strict_types=1);

namespace Omega\Container\Definition\ObjectDefinition;

/**
 * Describe an injection in a class property.
 */
class PropertyInjection
{
    public string $propertyName {
        get {
            return $this->propertyName;
        }
    }

    /** @var mixed Value that should be injected in the property. */
    public mixed $value {
        get {
            return $this->value;
        }
    }

    /**
     * Use for injecting in properties of parent classes: the class name
     * must be the name of the parent class because private properties
     * can be attached to the parent classes, not the one we are resolving.
     *
     * @var string|null
     */
    public ?string $className {
        get {
            return $this->className;
        }
    }

    /**
     * @param string      $propertyName Property name
     * @param mixed       $value Value that should be injected in the property
     * @param string|null $className
     */
    public function __construct(string $propertyName, mixed $value, ?string $className = null)
    {
        $this->propertyName = $propertyName;
        $this->value        = $value;
        $this->className    = $className;
    }

    /**
     * @param callable $replacer
     * @return void
     */
    public function replaceNestedDefinition(callable $replacer) : void
    {
        $this->value = $replacer($this->value);
    }
}
