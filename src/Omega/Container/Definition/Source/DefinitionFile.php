<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use Exception;
use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;

use function is_array;
use function sprintf;

/**
 * Reads DI definitions from a file returning a PHP array.
 */
class DefinitionFile extends DefinitionArray
{
    private bool $initialized = false;

    /**
     * @param string $file File in which the definitions are returned as an array.
     * @param AutowiringInterface|null $autowiring
     * @return void
     * @throws Exception
     */
    public function __construct(
        private readonly string $file,
        ?AutowiringInterface $autowiring = null,
    ) {
        // Lazy-loading to improve performances
        parent::__construct([], $autowiring);
    }

    /**
     * @param string $name
     * @return DefinitionInterface|null
     * @throws InvalidDefinitionException
     * @throws Exception
     */
    public function getDefinition(string $name): ?DefinitionInterface
    {
        $this->initialize();

        return parent::getDefinition($name);
    }

    /**
     * @return array|DefinitionInterface[]
     * @throws Exception
     */
    public function getDefinitions(): array
    {
        $this->initialize();

        return parent::getDefinitions();
    }

    /**
     * Lazy-loading of the definitions.
     */
    /**
     * @return void
     * @throws Exception
     */
    private function initialize(): void
    {
        if ($this->initialized === true) {
            return;
        }

        $definitions = require $this->file;

        if (!is_array($definitions)) {
            throw new Exception(
                sprintf(
                    "File '%s' should return an array of definitions",
                    $this->file
                )
            );
        }

        $this->addDefinitions($definitions);

        $this->initialized = true;
    }
}
