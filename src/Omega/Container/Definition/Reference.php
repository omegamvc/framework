<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

use Omega\Container\ContainerInterface;
use Omega\Container\Exceptions\ContainerExceptionInterface;
use Omega\Container\Exceptions\NotFoundExceptionInterface;
use function sprintf;

/**
 * Represents a reference to another entry.
 */
class Reference implements DefinitionInterface, SelfResolvingDefinitionInterface
{
    /** Entry name. */
    private string $name = '';

    /**
     * @param string $targetEntryName Name of the target entry
     */
    public function __construct(
        private readonly string $targetEntryName,
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
    public function getTargetEntryName() : string
    {
        return $this->targetEntryName;
    }

    /**
     * @param ContainerInterface $container
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function resolve(ContainerInterface $container) : mixed
    {
        return $container->get($this->getTargetEntryName());
    }

    /**
     * @param ContainerInterface $container
     * @return bool
     */
    public function isResolvable(ContainerInterface $container) : bool
    {
        return $container->has($this->getTargetEntryName());
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
        return sprintf(
            'get(%s)',
            $this->targetEntryName
        );
    }
}
