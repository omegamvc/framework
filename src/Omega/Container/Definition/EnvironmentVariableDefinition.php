<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

use function sprintf;
use function str_replace;
use function var_export;

use const PHP_EOL;

/**
 * Defines a reference to an environment variable, with fallback to a default
 * value if the environment variable is not defined.
 */
class EnvironmentVariableDefinition implements DefinitionInterface
{
    /** Entry name. */
    private string $name = '';

    /**
     * @param string $variableName The name of the environment variable
     * @param bool   $isOptional   Whether the environment variable definition is optional.
     *                             If true and the environment variable given by $variableName has not been defined,
     *                             $defaultValue is used.
     * @param mixed $defaultValue The default value to use if the environment variable is optional and not provided
     */
    public function __construct(
        public string $variableName {
        get {
        return $this->variableName;
        }
        },
        public bool $isOptional = false {
        get {
        return $this->isOptional;
        }
        },
        public mixed $defaultValue = null {
        get {
        return $this->defaultValue;
        }
        },
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param callable $replacer
     * @return void
     */
    public function replaceNestedDefinitions(callable $replacer): void
    {
        $this->defaultValue = $replacer($this->defaultValue);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $str = '    variable = ' . $this->variableName . PHP_EOL
            . '    optional = ' . ($this->isOptional ? 'yes' : 'no');

        if ($this->isOptional) {
            if ($this->defaultValue instanceof DefinitionInterface) {
                $nestedDefinition = (string) $this->defaultValue;
                $defaultValueStr = str_replace(PHP_EOL, PHP_EOL . '    ', $nestedDefinition);
            } else {
                $defaultValueStr = var_export($this->defaultValue, true);
            }

            $str .= PHP_EOL . '    default = ' . $defaultValueStr;
        }

        return sprintf('Environment variable (' . PHP_EOL . '%s' . PHP_EOL . ')', $str);
    }
}
