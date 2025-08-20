<?php

declare(strict_types=1);

namespace Omega\Container\Definition\Source;

use LogicException;
use Omega\Container\Definition\AutowireDefinition;
use Omega\Container\Definition\DefinitionInterface;
use Omega\Container\Definition\ObjectDefinition;
use function apcu_fetch;
use function apcu_store;
use function function_exists;
use function ini_get;
use const PHP_SAPI;

/**
 * Decorator that caches another definition source.
 */
class SourceCache implements DefinitionSourceInterface, MutableDefinitionSourceInterface
{
    public const string CACHE_KEY = 'php-di.definitions.';

    public function __construct(
        private readonly DefinitionSourceInterface $cachedSource,
        private readonly string                    $cacheNamespace = '',
    ) {
    }

    public function getDefinition(string $name) : ?DefinitionInterface
    {
        $definition = apcu_fetch($this->getCacheKey($name));

        if ($definition === false) {
            $definition = $this->cachedSource->getDefinition($name);

            // Update the cache
            if ($this->shouldBeCached($definition)) {
                apcu_store($this->getCacheKey($name), $definition);
            }
        }

        return $definition;
    }

    /**
     * Used only for the compilation so we can skip the cache safely.
     *
     * @return array
     */
    public function getDefinitions() : array
    {
        return $this->cachedSource->getDefinitions();
    }

    /**
     * @return bool
     */
    public static function isSupported() : bool
    {
        return function_exists('apcu_fetch')
            && ini_get('apc.enabled')
            && ! ('cli' === PHP_SAPI && ! ini_get('apc.enable_cli'));
    }

    /**
     * @param string $name
     * @return string
     */
    public function getCacheKey(string $name) : string
    {
        return self::CACHE_KEY . $this->cacheNamespace . $name;
    }

    /**
     * @param DefinitionInterface $definition
     * @return void
     */
    public function addDefinition(DefinitionInterface $definition) : void
    {
        throw new LogicException(
            'You cannot set a definition at runtime on a container that has caching enabled.' .
            'Doing so would risk caching the definition for the next execution, where it might be' .
            'different. You can either put your definitions in a file, remove the cache or ->set() a' .
            'raw value directly (PHP object, string, int, ...) instead of a PHP-DI definition.'
        );
    }

    /**
     * @param DefinitionInterface|null $definition
     * @return bool
     */
    private function shouldBeCached(?DefinitionInterface $definition = null) : bool
    {
        return
            // Cache missing definitions
            ($definition === null)
            // Object definitions are used with `make()`
            || ($definition instanceof ObjectDefinition)
            // Autowired definitions cannot be all compiled and are used with `make()`
            || ($definition instanceof AutowireDefinition);
    }
}
