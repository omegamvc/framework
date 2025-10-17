<?php

declare(strict_types=1);

namespace Omega\Container\Definition;

use Omega\Container\Definition\Exceptions\InvalidDefinitionException;

use function array_merge;

/**
 * Extends an array definition by adding new elements into it.
 */
class ArrayDefinitionExtension extends ArrayDefinition implements ExtendsPreviousDefinitionInterface
{
    private ?ArrayDefinition $subDefinition = null;

    /**
     * @return array
     */
    public function getValues(): array
    {
        if (! $this->subDefinition) {
            return parent::getValues();
        }

        return array_merge($this->subDefinition->getValues(), parent::getValues());
    }

    /**
     * @param DefinitionInterface $definition
     * @return void
     * @throws InvalidDefinitionException
     */
    public function setExtendedDefinition(DefinitionInterface $definition): void
    {
        if (!$definition instanceof ArrayDefinition) {
            throw new InvalidDefinitionException(sprintf(
                'Definition %s tries to add array entries but the previous definition is not an array',
                $this->getName()
            ));
        }

        $this->subDefinition = $definition;
    }
}
