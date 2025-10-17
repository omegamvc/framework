<?php

declare(strict_types=1);

namespace Omega\Console\Commands;

use Omega\Console\AbstractCommand;
use Omega\Application\Application;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Router\Router;
use Omega\SerializableClosure\UnsignedSerializableClosure;

use function Omega\Console\error;
use function Omega\Console\success;
use function Omega\Console\warn;

class RouteCacheCommand extends AbstractCommand
{
    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'pattern' => 'route:cache',
            'fn'      => [self::class, 'cache'],
        ], [
            'pattern' => 'route:clear',
            'fn'      => [self::class, 'clear'],
        ],
    ];

    /**
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'route:cache' => 'Build route cache',
                'route:clear' => 'Remove route cache',
            ],
            'options'   => [
                '--files' => 'Cache specific router files.',
            ],
            'relation'  => [
                'route:cache' => ['--files'],
            ],
        ];
    }

    /**
     * @throws InvalidDefinitionException
     * @throws NotFoundException
     * @throws DependencyException
     */
    public function cache(Application $app, Router $router): int
    {
        if (false !== ($files = $this->option('files', false))) {
            $router->Reset();
            $files = is_string($files) ? [$files] : $files;
            foreach ($files as $file) {
                if (false === file_exists(get_path('path.base') . $file)) {
                    warn("Route file cant be load '$file'.")->out();

                    return 1;
                }

                require get_path('path.base') . $file;
            }
        }

        $routes = [];
        /**foreach ($router->getRoutesRaw() as $route) {
            if (is_callable($route['function'])) {
                warn("Route '{$route['name']}' cannot be cached because it contains a closure/callback function")
                    ->out();

                return 1;
            }

            $routes[] = [
                'method'     => $route['method'],
                'uri'        => $route['uri'],
                'expression' => $route['expression'],
                'function'   => $route['function'],
                'middleware' => $route['middleware'],
                'name'       => $route['name'],
                'patterns'   => $route['patterns'] ?? [],
            ];
        }*/
        foreach ($router->getRoutesRaw() as $route) {
            if (is_callable($route['function'])) {
                $routeFunction = serialize(new UnsignedSerializableClosure($route['function']));
            } else {
                $routeFunction = $route['function'];
            }

            $routes[] = [
                'method'     => $route['method'],
                'uri'        => $route['uri'],
                'expression' => $route['expression'],
                'function'   => $routeFunction,
                'middleware' => $route['middleware'],
                'name'       => $route['name'],
                'patterns'   => $route['patterns'] ?? [],
            ];
        }
        $cached_route = '<?php return ' . var_export($routes, true) . ';' . PHP_EOL;
        if (file_put_contents($app->getApplicationCachePath() . 'route.php', $cached_route)) {
            success('Route file has successfully created.')->out();

            return 0;
        }
        error('Cant build route cache.')->out();

        return 1;
    }

    /**
     * @throws InvalidDefinitionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function clear(Application $app): int
    {
        if (file_exists($file = $app->getApplicationCachePath() . 'route.php')) {
            @unlink($file);
            success('Clear route cache has successfully.')->out();

            return 0;
        }

        return 1;
    }
}
