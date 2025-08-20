<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\ObjectDefinition;

use function sprintf;

/**
 * Implementation used when autowiring is completely disabled.
 */
class NoAutowiring implements AutowiringInterface
{
    /**
     * @param string $name
     * @param ObjectDefinition|null $definition
     * @return ObjectDefinition|null
     * @throws InvalidDefinitionException
     */
    public function autowire(string $name, ?ObjectDefinition $definition = null) : ?ObjectDefinition
    {
        throw new InvalidDefinitionException(
            sprintf(
                'Cannot autowire entry "%s" because autowiring is disabled',
                $name
            )
        );
    }
}
