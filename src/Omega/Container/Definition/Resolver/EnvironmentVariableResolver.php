<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Resolver;

use Omega\Container\Definition\Definition;
use Omega\Container\Definition\EnvironmentVariableDefinition;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use function call_user_func;

/**
 * Resolves a environment variable definition to a value.
 *
 * @template-implements DefinitionResolverInterface<EnvironmentVariableDefinition>
 */
class EnvironmentVariableResolver implements DefinitionResolverInterface
{
    /** @var callable */
    private $variableReader;

    /**
     * @param DefinitionResolverInterface $definitionResolver
     * @param callable|null $variableReader
     */
    public function __construct(
        private readonly DefinitionResolverInterface $definitionResolver,
        ?callable $variableReader = null
    ) {
        $this->variableReader = $variableReader ?? [$this, 'getEnvVariable'];
    }

    /**
     * Resolve an environment variable definition to a value.
     *
     * @param EnvironmentVariableDefinition $definition
     * @param array $parameters
     * @return mixed
     * @throws InvalidDefinitionException
     * @throws DependencyException
     */
    public function resolve(Definition $definition, array $parameters = []) : mixed
    {
        $value = call_user_func($this->variableReader, $definition->getVariableName());

        if (false !== $value) {
            return $value;
        }

        if (!$definition->isOptional()) {
            throw new InvalidDefinitionException(sprintf(
                "The environment variable '%s' has not been defined",
                $definition->getVariableName()
            ));
        }

        $value = $definition->getDefaultValue();

        // Nested definition
        if ($value instanceof Definition) {
            return $this->definitionResolver->resolve($value);
        }

        return $value;
    }

    /**
     * @param Definition $definition
     * @param array $parameters
     * @return bool
     */
    public function isResolvable(Definition $definition, array $parameters = []) : bool
    {
        return true;
    }

    /**
     * @param string $variableName
     * @return array|false|mixed|string
     */
    protected function getEnvVariable(string $variableName): mixed
    {
        return $_ENV[$variableName] ?? $_SERVER[$variableName] ?? getenv($variableName);
    }
}
