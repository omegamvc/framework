<?php /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Router;

use Omega\Collection\Collection;
use Omega\Collection\CollectionImmutable;
use function array_key_exists;

class ResourceController
{
    /** @var Collection<string, Route> */
    private Collection $resource;

    /**
     * List resource method.
     *
     * @return array<string, string>
     */
    public static function method(): array
    {
        return [
            'index'   => 'index',
            'create'  => 'create',
            'store'   => 'store',
            'show'    => 'show',
            'edit'    => 'edit',
            'update'  => 'update',
            'destroy' => 'destroy',
        ];
    }

    /**
     * @param string                $url
     * @param class-string          $className
     * @param array<string, string> $map
     */
    public function __construct(string $url, string $className, array $map)
    {
        $this->resource = new Collection([]);
        $this->generate($url, $className, $map);
    }

    /**
     * @param string                $uri
     * @param class-string $className
     * @param array<string, string> $map
     * @return self
     */
    public function generate(string $uri, string $className, array $map): self
    {
        $uri  = Router::$group['prefix'] . $uri;

        if (array_key_exists('index', $map)) {
            $this->resource->set($map['index'],
                (new Route([
                    'expression' => Router::mapPatterns($uri),
                    'function'   => [$className, $map['index']],
                    'method'     => 'get',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$className}.index")
            );
        }

        if (array_key_exists('create', $map)) {
            $this->resource->set($map['create'],
                (new Route([
                    'expression' => Router::mapPatterns("{$uri}create"),
                    'function'   => [$className, $map['create']],
                    'method'     => 'get',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$className}.create")
            );
        }

        if (array_key_exists('store', $map)) {
            $this->resource->set($map['store'],
                (new Route([
                    'expression' => Router::mapPatterns($uri),
                    'function'   => [$className, $map['store']],
                    'method'     => 'post',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$className}.store")
            );
        }

        if (array_key_exists('show', $map)) {
            $this->resource->set($map['show'],
                (new Route([
                    'expression' => Router::mapPatterns("{$uri}(:id)"),
                    'function'   => [$className, $map['show']],
                    'method'     => 'get',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$className}.show")
            );
        }

        if (array_key_exists('edit', $map)) {
            $this->resource->set($map['edit'],
                (new Route([
                    'expression' => Router::mapPatterns("{$uri}(:id)/edit"),
                    'function'   => [$className, $map['edit']],
                    'method'     => 'get',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$className}.edit")
            );
        }

        if (array_key_exists('update', $map)) {
            $this->resource->set($map['update'],
                (new Route([
                    'expression' => Router::mapPatterns("{$uri}(:id)"),
                    'function'   => [$className, $map['update']],
                    'method'     => ['put', 'patch'],
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$className}.update")
            );
        }

        if (array_key_exists('destroy', $map)) {
            $this->resource->set($map['destroy'],
                (new Route([
                    'expression' => Router::mapPatterns("{$uri}(:id)"),
                    'function'   => [$className, $map['destroy']],
                    'method'     => 'delete',
                    'middleware' => Router::$group['middleware'] ?? [],
                ]))->name("{$className}.destroy")
            );
        }

        return $this;
    }

    /**
     * @return CollectionImmutable<string, Route>
     */
    public function get(): CollectionImmutable
    {
        return $this->resource->immutable();
    }

    /**
     * @param string[] $resource
     * @return self
     */
    public function only(array $resource): self
    {
        $this->resource->only($resource);

        return $this;
    }

    /**
     * @param string[] $resource
     * @return self
     */
    public function except(array $resource): self
    {
        $this->resource->except($resource);

        return $this;
    }
}
