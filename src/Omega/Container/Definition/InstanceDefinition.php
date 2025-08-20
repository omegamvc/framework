<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

/**
 * Defines injections on an existing class instance.
 */
class InstanceDefinition implements DefinitionInterface
{
    /**
     * @param object $instance Instance on which to inject dependencies.
     */
    public function __construct(
        private readonly object $instance,
        public ObjectDefinition $objectDefinition {
            get {
                return $this->objectDefinition;
            }
        },
    ) {
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        // Name are superfluous for instance definitions
        return '';
    }

    public function setName(string $name) : void
    {
        // Name are superfluous for instance definitions
    }

    /**
     * @return object
     */
    public function getInstance() : object
    {
        return $this->instance;
    }

    /**
     * @param callable $replacer
     * @return void
     */
    public function replaceNestedDefinitions(callable $replacer) : void
    {
        $this->objectDefinition->replaceNestedDefinitions($replacer);
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return 'Instance';
    }
}
