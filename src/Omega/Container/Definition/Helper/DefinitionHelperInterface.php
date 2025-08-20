<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Helper;

use Omega\Container\Definition\DefinitionInterface;

/**
 * Helps defining container entries.
 */
interface DefinitionHelperInterface
{
    /**
     * @param string $entryName Container entry name
     */
    public function getDefinition(string $entryName) : DefinitionInterface;
}
