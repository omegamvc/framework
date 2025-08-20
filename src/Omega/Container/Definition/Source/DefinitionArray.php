<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use Exception;
use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;

use function array_filter;
use function array_key_exists;
use function array_shift;
use function preg_match;
use function preg_quote;
use function str_contains;
use function str_replace;

use const ARRAY_FILTER_USE_KEY;

/**
 * Reads DI definitions from a PHP array.
 */
class DefinitionArray implements DefinitionSourceInterface, MutableDefinitionSourceInterface
{
    /** @var string  */
    public const string WILDCARD = '*';

    /** @var string Matches anything except "\". */
    private const string WILDCARD_PATTERN = '([^\\\\]+)';

    /** @var array DI definitions in a PHP array. */
    private array $definitions;

    /** @var array|null Cache of wildcard definitions. */
    private ?array $wildcardDefinitions = null;

    /** @var DefinitionNormalizer  */
    private DefinitionNormalizer $normalizer;

    /**
     * @param array $definitions
     * @param AutowiringInterface|null $autowiring
     * @throws Exception
     */
    public function __construct(array $definitions = [], ?AutowiringInterface $autowiring = null)
    {
        if (isset($definitions[0])) {
            throw new Exception(
                'The Container definition is not indexed by an entry name in the definition array'
            );
        }

        $this->definitions = $definitions;

        $this->normalizer = new DefinitionNormalizer($autowiring ?: new NoAutowiring);
    }

    /**
     * @param array $definitions DI definitions in a PHP array indexed by the definition name.
     * @return void
     * @throws Exception
     */
    public function addDefinitions(array $definitions) : void
    {
        if (isset($definitions[0])) {
            throw new Exception(
                'The Container definition is not indexed by an entry name in the definition array'
            );
        }

        // The newly added data prevails
        // "for keys that exist in both arrays, the elements from the left-hand array will be used"
        $this->definitions = $definitions + $this->definitions;

        // Clear cache
        $this->wildcardDefinitions = null;
    }

    /**
     * @param DefinitionInterface $definition
     * @return void
     */
    public function addDefinition(DefinitionInterface $definition) : void
    {
        $this->definitions[$definition->getName()] = $definition;

        // Clear cache
        $this->wildcardDefinitions = null;
    }

    /**
     * @param string $name
     * @return DefinitionInterface|null
     * @throws InvalidDefinitionException
     */
    public function getDefinition(string $name) : ?DefinitionInterface
    {
        // Look for the definition by name
        if (array_key_exists($name, $this->definitions)) {
            $definition = $this->definitions[$name];

            return $this->normalizer->normalizeRootDefinition($definition, $name);
        }

        // Build the cache of wildcard definitions
        if ($this->wildcardDefinitions === null) {
            $this->wildcardDefinitions = [];
            foreach ($this->definitions as $key => $definition) {
                if (str_contains($key, self::WILDCARD)) {
                    $this->wildcardDefinitions[$key] = $definition;
                }
            }
        }

        // Look in wildcards definitions
        foreach ($this->wildcardDefinitions as $key => $definition) {
            // Turn the pattern into a regex
            $key = preg_quote($key, '#');
            $key = '#^' . str_replace('\\' . self::WILDCARD, self::WILDCARD_PATTERN, $key) . '#';
            if (preg_match($key, $name, $matches) === 1) {
                array_shift($matches);

                return $this->normalizer->normalizeRootDefinition($definition, $name, $matches);
            }
        }

        return null;
    }

    /**
     * @return array|DefinitionInterface[]
     */
    public function getDefinitions() : array
    {
        // Return all definitions except wildcard definitions
        return array_filter($this->definitions, function ($key) {
            return !str_contains($key, self::WILDCARD);
        }, ARRAY_FILTER_USE_KEY);
    }
}
