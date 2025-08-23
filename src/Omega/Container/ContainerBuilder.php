<?php

declare(strict_types=1);

namespace Omega\Container;

use Exception;
use LogicException;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Definition\Source\AttributeBasedAutowiring;
use Omega\Container\Definition\Source\DefinitionArray;
use Omega\Container\Definition\Source\DefinitionFile;
use Omega\Container\Definition\Source\DefinitionSourceInterface;
use Omega\Container\Definition\Source\NoAutowiring;
use Omega\Container\Definition\Source\ReflectionBasedAutowiring;
use Omega\Container\Definition\Source\SourceCache;
use Omega\Container\Definition\Source\SourceChain;
use Omega\Container\Proxy\NativeProxyFactory;

use function array_map;
use function array_reverse;
use function is_array;
use function is_string;

/**
 * Helper to create and configure a Container.
 *
 * With the default options, the container created is appropriate for the development environment.
 *
 * Example:
 * ```php
 *     $builder = new ContainerBuilder();
 *     $container = $builder->build();
 * ```
 */
class ContainerBuilder
{
    /** @var class-string<Container> Name of the container class, used to create the container. */
    private string $containerClass;

    /** @var bool */
    private bool $useAutowiring = true;

    /** @var bool */
    private bool $useAttributes = false;

    /** @var ContainerInterface|null If PHP-DI is wrapped in another container, this references the wrapper. */
    private ?ContainerInterface $wrapperContainer = null;

    /** @var DefinitionSourceInterface[]|string[]|array[] */
    private array $definitionSources = [];

    /** @var bool Whether the container has already been built. */
    private bool $locked = false;

    /** @var bool  */
    private bool $sourceCache = false;

    /** @var string  */
    protected string $sourceCacheNamespace = '';

    /**
     * @param class-string<Container> $containerClass Name of the container class, used to create the container.
     */
    public function __construct(string $containerClass = Container::class)
    {
        $this->containerClass = $containerClass;
    }

    /**
     * Build and return a container.
     *
     * @return Container
     * @throws InvalidDefinitionException
     * @throws Exception
     */
    public function build(): Container
    {
        $sources = array_reverse($this->definitionSources);

        if ($this->useAttributes) {
            $autowiring = new AttributeBasedAutowiring;
            $sources[] = $autowiring;
        } elseif ($this->useAutowiring) {
            $autowiring = new ReflectionBasedAutowiring;
            $sources[] = $autowiring;
        } else {
            $autowiring = new NoAutowiring;
        }

        $sources = array_map(function ($definitions) use ($autowiring) {
            if (is_string($definitions)) {
                // File
                return new DefinitionFile($definitions, $autowiring);
            }
            if (is_array($definitions)) {
                return new DefinitionArray($definitions, $autowiring);
            }

            return $definitions;
        }, $sources);
        $source = new SourceChain($sources);

        // Mutable definition source
        $source->setMutableDefinitionSource(new DefinitionArray([], $autowiring));

        if ($this->sourceCache) {
            if (!SourceCache::isSupported()) {
                throw new Exception('APCu is not enabled, Omega\Container cannot use it as a cache');
            }
            // Wrap the source with the cache decorator
            $source = new SourceCache($source, $this->sourceCacheNamespace);
        }

		$proxyFactory = new NativeProxyFactory();

        $this->locked = true;

        $containerClass = $this->containerClass;

        return new $containerClass($source, $proxyFactory, $this->wrapperContainer);
    }

    /**
     * Enable or disable the use of autowiring to guess injections.
     *
     * Enabled by default.
     *
     * @param bool $bool
     * @return $this
     */
    public function useAutowiring(bool $bool) : self
    {
        $this->ensureNotLocked();

        $this->useAutowiring = $bool;

        return $this;
    }

    /**
     * Enable or disable the use of PHP 8 attributes to configure injections.
     *
     * Disabled by default.
     *
     * @param bool $bool
     * @return $this
     */
    public function useAttributes(bool $bool) : self
    {
        $this->ensureNotLocked();

        $this->useAttributes = $bool;

        return $this;
    }

    /**
     * If PHP-DI's container is wrapped by another container, we can
     * set this so that PHP-DI will use the wrapper rather than itself for building objects.
     *
     * @param ContainerInterface $otherContainer
     * @return $this
     */
    public function wrapContainer(ContainerInterface $otherContainer) : self
    {
        $this->ensureNotLocked();

        $this->wrapperContainer = $otherContainer;

        return $this;
    }

    /**
     * Add definitions to the container.
     *
     * @param string|array|DefinitionSourceInterface ...$definitions Can be an array of definitions, the
     *                                                      name of a file containing definitions
     *                                                      or a DefinitionSource object.
     * @return $this
     */
    public function addDefinitions(string|array|DefinitionSourceInterface ...$definitions) : self
    {
        $this->ensureNotLocked();

        foreach ($definitions as $definition) {
            $this->definitionSources[] = $definition;
        }

        return $this;
    }

    /**
     * Enables the use of APCu to cache definitions.
     *
     * You must have APCu enabled to use it.
     *
     * Before using this feature, you should try these steps first:

     * - if you use autowiring or attributes, add all the classes you are using into your configuration so that
     *   PHP-DI knows about them and compiles them
     * Once this is done, you can try to optimize performances further with APCu. It can also be useful if you use
     * `Container::make()` instead of `get()` (`make()` calls cannot be compiled so they are not optimized).
     *
     * Remember to clear APCu on each deploy else your application will have a stale cache. Do not enable the cache
     * in development environment: any change you will make to the code will be ignored because of the cache.
     *
     * @param string $cacheNamespace use unique namespace per container when sharing a single APC memory pool
     *                               to prevent cache collisions
     * @return $this
     */
    public function enableDefinitionCache(string $cacheNamespace = '') : self
    {
        $this->ensureNotLocked();

        $this->sourceCache = true;
        $this->sourceCacheNamespace = $cacheNamespace;

        return $this;
    }

    /**
     * @return void
     */
    private function ensureNotLocked() : void
    {
        if ($this->locked) {
            throw new LogicException(
                'The ContainerBuilder cannot be modified after the container has been built'
            );
        }
    }
}
