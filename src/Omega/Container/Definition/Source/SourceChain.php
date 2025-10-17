<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use LogicException;
use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\ExtendsPreviousDefinitionInterface;

use function array_combine;
use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function count;

/**
 * Manages a chain of other definition sources.
 */
class SourceChain implements DefinitionSourceInterface, MutableDefinitionSourceInterface
{
    private ?MutableDefinitionSourceInterface $mutableSource;

    /**
     * @param list<DefinitionSourceInterface> $sources
     */
    public function __construct(
        private array $sources,
    ) {
    }

    /**
     * @param string $name
     * @param int $startIndex Use this parameter to start looking from a specific
     *                        point in the source chain.
     * @return DefinitionInterface|null
     */
    /**
     * @param string $name
     * @param int $startIndex
     * @return DefinitionInterface|null
     * @throws InvalidDefinitionException
     */
    public function getDefinition(string $name, int $startIndex = 0): ?DefinitionInterface
    {
        $count = count($this->sources);
        for ($i = $startIndex; $i < $count; ++$i) {
            $source = $this->sources[$i];

            $definition = $source->getDefinition($name);

            if ($definition) {
                if ($definition instanceof ExtendsPreviousDefinitionInterface) {
                    $this->resolveExtendedDefinition($definition, $i);
                }

                return $definition;
            }
        }

        return null;
    }

    /**
     * @return array|DefinitionInterface[]
     * @throws InvalidDefinitionException
     */
    public function getDefinitions(): array
    {
        $allDefinitions = array_merge(...array_map(fn ($source) => $source->getDefinitions(), $this->sources));

        /** @var string[] $allNames */
        $allNames = array_keys($allDefinitions);

        $allValues = array_filter(array_map(fn ($name) => $this->getDefinition($name), $allNames));

        return array_combine($allNames, $allValues);
    }

    /**
     * @param DefinitionInterface $definition
     * @return void
     */
    public function addDefinition(DefinitionInterface $definition): void
    {
        if (! $this->mutableSource) {
            throw new LogicException(
                "The container's definition source has not been initialized correctly"
            );
        }

        $this->mutableSource->addDefinition($definition);
    }

    /**
     * @param ExtendsPreviousDefinitionInterface $definition
     * @param int $currentIndex
     * @return void
     * @throws InvalidDefinitionException
     */
    private function resolveExtendedDefinition(ExtendsPreviousDefinitionInterface $definition, int $currentIndex): void
    {
        // Look in the next sources only (else infinite recursion, and we can only extend
        // entries defined in the previous definition files - a previous == next here because
        // the array was reversed ;) )
        $subDefinition = $this->getDefinition($definition->getName(), $currentIndex + 1);

        if ($subDefinition) {
            $definition->setExtendedDefinition($subDefinition);
        }
    }

    /**
     * @param MutableDefinitionSourceInterface $mutableSource
     * @return void
     */
    public function setMutableDefinitionSource(MutableDefinitionSourceInterface $mutableSource): void
    {
        $this->mutableSource = $mutableSource;

        array_unshift($this->sources, $mutableSource);
    }
}
