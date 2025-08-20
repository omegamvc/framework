<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use Closure;
use Omega\Container\Definition\ArrayDefinition;
use Omega\Container\Definition\AutowireDefinition;
use Omega\Container\Definition\DecoratorDefinition;
use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\FactoryDefinition;
use Omega\Container\Definition\Helper\DefinitionHelperInterface;
use Omega\Container\Definition\ObjectDefinition;
use Omega\Container\Definition\ValueDefinition;
use function is_array;

/**
 * Turns raw definitions/definition helpers into definitions ready
 * to be resolved or compiled.
 */
readonly class DefinitionNormalizer
{
    public function __construct(
        private AutowiringInterface $autowiring,
    ) {
    }

    /**
     * Normalize a definition that is *not* nested in another one.
     *
     * This is usually a definition declared at the root of a definition array.
     *
     * @param string $name The definition name.
     * @param string[] $wildcardsReplacements Replacements for wildcard definitions.
     *
     * @throws InvalidDefinitionException
     */
    public function normalizeRootDefinition(mixed $definition, string $name, ?array $wildcardsReplacements = null) : DefinitionInterface
    {
        if ($definition instanceof DefinitionHelperInterface) {
            $definition = $definition->getDefinition($name);
        } elseif (is_array($definition)) {
            $definition = new ArrayDefinition($definition);
        } elseif ($definition instanceof Closure) {
            $definition = new FactoryDefinition($name, $definition);
        } elseif (! $definition instanceof DefinitionInterface) {
            $definition = new ValueDefinition($definition);
        }

        // For a class definition, we replace * in the class name with the matches
        // *Interface -> *Impl => FooInterface -> FooImpl
        if ($wildcardsReplacements && $definition instanceof ObjectDefinition) {
            $definition->replaceWildcards($wildcardsReplacements);
        }

        if ($definition instanceof AutowireDefinition) {
            /** @var AutowireDefinition $definition */
            $definition = $this->autowiring->autowire($name, $definition);
        }

        $definition->setName($name);

        try {
            $definition->replaceNestedDefinitions([$this, 'normalizeNestedDefinition']);
        } catch (InvalidDefinitionException $e) {
            throw InvalidDefinitionException::create($definition, sprintf(
                'Definition "%s" contains an error: %s',
                $definition->getName(),
                $e->getMessage()
            ), $e);
        }

        return $definition;
    }

    /**
     * Normalize a definition that is nested in another one.
     *
     * @throws InvalidDefinitionException
     */
    public function normalizeNestedDefinition(mixed $definition) : mixed
    {
        $name = '<nested definition>';

        if ($definition instanceof DefinitionHelperInterface) {
            $definition = $definition->getDefinition($name);
        } elseif (is_array($definition)) {
            $definition = new ArrayDefinition($definition);
        } elseif ($definition instanceof Closure) {
            $definition = new FactoryDefinition($name, $definition);
        }

        if ($definition instanceof DecoratorDefinition) {
            throw new InvalidDefinitionException('Decorators cannot be nested in another definition');
        }

        if ($definition instanceof AutowireDefinition) {
            $definition = $this->autowiring->autowire($name, $definition);
        }

        if ($definition instanceof DefinitionInterface) {
            $definition->setName($name);

            // Recursively traverse nested definitions
            $definition->replaceNestedDefinitions([$this, 'normalizeNestedDefinition']);
        }

        return $definition;
    }
}
