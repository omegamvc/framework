<?php

declare(strict_types=1);

namespace Omega\Container\Exceptions;

use Exception;

/**
 * Exception for the Container.
 */
class DependencyException extends Exception implements ContainerExceptionInterface
{
}
