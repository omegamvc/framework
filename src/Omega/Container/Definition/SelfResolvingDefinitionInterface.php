<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

use Omega\Container\ContainerInterface;

/**
 * Describes a definition that can resolve itself.
 */
interface SelfResolvingDefinitionInterface
{
    /**
     * Resolve the definition and return the resulting value.
     *
     * @param ContainerInterface $container
     * @return mixed
     */
    public function resolve(ContainerInterface $container) : mixed;

    /**
     * Check if a definition can be resolved.
     *
     * @param ContainerInterface $container
     * @return bool
     */
    public function isResolvable(ContainerInterface $container) : bool;
}
