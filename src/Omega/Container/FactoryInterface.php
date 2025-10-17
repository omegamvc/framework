<?php

declare(strict_types=1);

namespace Omega\Container;

use InvalidArgumentException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;

/**
 * Describes the basic interface of a factory.
 */
interface FactoryInterface
{
    /**
     * Resolves an entry by its name. If given a class name, it will return a new instance of that class.
     *
     * @param string $name       Entry name or a class name.
     * @param array  $parameters Optional parameters to use to build the entry. Use this to force specific
     *                           parameters to specific values. Parameters not defined in this array will
     *                           be automatically resolved.
     * @throws InvalidArgumentException The name parameter must be of type string.
     * @throws DependencyException       Error while resolving the entry.
     * @throws NotFoundException         No entry or class found for the given name.
     */
    public function make(string $name, array $parameters = []): mixed;
}
