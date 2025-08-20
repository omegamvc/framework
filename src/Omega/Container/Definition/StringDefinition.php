<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

use Omega\Container\Exceptions\ContainerExceptionInterface;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\ContainerInterface;
use Omega\Container\Exceptions\NotFoundExceptionInterface;
use RuntimeException;
use function preg_replace_callback;
use function sprintf;

/**
 * Definition of a string composed of other strings.
 */
class StringDefinition implements DefinitionInterface, SelfResolvingDefinitionInterface
{
    /** Entry name. */
    private string $name = '';

    public function __construct(
        private readonly string $expression,
    ) {
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
     * @return string
     */
    public function getExpression() : string
    {
        return $this->expression;
    }

    /**
     * @param ContainerInterface $container
     * @return string
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     */
    public function resolve(ContainerInterface $container) : string
    {
        return self::resolveExpression($this->name, $this->expression, $container);
    }

    /**
     * @param ContainerInterface $container
     * @return bool
     */
    public function isResolvable(ContainerInterface $container) : bool
    {
        return true;
    }

    /**
     * @param callable $replacer
     * @return void
     */
    public function replaceNestedDefinitions(callable $replacer) : void
    {
        // no nested definitions
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->expression;
    }

    /**
     * Resolve a string expression.
     *
     * @param string $entryName
     * @param string $expression
     * @param ContainerInterface $container
     * @return string
     * @throws DependencyException
     * @throws ContainerExceptionInterface
     */
    public static function resolveExpression(
        string $entryName,
        string $expression,
        ContainerInterface $container,
    ) : string {
        $callback = function (array $matches) use ($entryName, $container) {
            try {
                return $container->get($matches[1]);
            } catch (NotFoundExceptionInterface $e) {
                throw new DependencyException(sprintf(
                    "Error while parsing string expression for entry '%s': %s",
                    $entryName,
                    $e->getMessage()
                ), 0, $e);
            }
        };

        $result = preg_replace_callback('#\{([^{}]+)}#', $callback, $expression);
        if ($result === null) {
            throw new RuntimeException(
                sprintf(
                    'An unknown error occurred while parsing the string definition: \'%s\'',
                    $expression
                )
            );
        }

        return $result;
    }
}
