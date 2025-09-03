<?php

declare(strict_types=1);

namespace Omega\Router;

use Omega\Http\Request;
use function array_filter;
use function array_shift;
use function is_numeric;
use function parse_url;
use function preg_match;
use function rtrim;
use function strtolower;
use const ARRAY_FILTER_USE_KEY;

final class RouteDispatcher
{
    /** @var Request */
    private Request $request;

    /** @var Route[] */
    private array $routes;

    /** @var callable */
    private $found;

    /** @var ?callable(string): mixed */
    private $notFound;

    /** @var ?callable(string, string): mixed */
    private $methodNotAllowed;

    private string $basepath = '';

    private bool $caseMatters = false;

    private bool $trailingSlashMatters = false;

    private bool $multiMatch = false;

    /** @var array<string, mixed> */
    private array $trigger;

    /** @var Route */
    private Route $current;

    /**
     * @param Request $request Incoming request
     * @param Route[] $routes  Array of route
     */
    public function __construct(Request $request, array $routes)
    {
        $this->request = $request;
        $this->routes  = $routes;
    }

    /**
     * Create new construct using uri and method.
     *
     * @param string  $uri    Ulr
     * @param string  $method Method
     * @param Route[] $routes Array of route
     */
    public static function dispatchFrom(string $uri, string $method, array $routes): self
    {
        $createRequest = new Request($uri, [], [], [], [], [], [], $method);

        return new RouteDispatcher($createRequest, $routes);
    }

    /**
     * Setup Base Path.
     *
     * @param string $basePath Base Path
     * @return self
     */
    public function basePath(string $basePath): self
    {
        $this->basepath = $basePath;

        return $this;
    }

    /**
     * Case-sensitive matters.
     *
     * @param bool $case_matters Case-sensitive matters
     * @return self
     */
    public function caseMatters(bool $case_matters): self
    {
        $this->caseMatters = $case_matters;

        return $this;
    }

    /**
     * Trailing slash matters.
     *
     * @param bool $trailingSlashMatters Trailing slash matters
     * @return self
     */
    public function trailingSlashMatters(bool $trailingSlashMatters): self
    {
        $this->trailingSlashMatters = $trailingSlashMatters;

        return $this;
    }

    /**
     * Return multi route.
     *
     * @param bool $multiMatch Return multi route
     * @return self
     */
    public function multiMatch(bool $multiMatch): self
    {
        $this->multiMatch = $multiMatch;

        return $this;
    }

    /**
     * Get current router after dispatch.
     *
     * @return Route
     */
    public function current(): Route
    {
        return $this->current;
    }

    /**
     * Setup action and dispatch route.
     *
     * @param callable $found
     * @param callable $notFound
     * @param callable $methodNotAllowed
     * @return array<string, mixed> trigger action ['callable' => callable, 'param' => param]
     */
    public function run(callable $found, callable $notFound, callable $methodNotAllowed): array
    {
        $this->found            = $found;
        $this->notFound         = $notFound;
        $this->methodNotAllowed = $methodNotAllowed;

        $this->dispatch($this->basepath, $this->caseMatters, $this->trailingSlashMatters, $this->multiMatch);

        return $this->trigger;
    }

    /**
     * Catch action from callable (found, not_found, method_not_allowed).
     *
     * @param callable                   $callable   Callback
     * @param array<int, mixed|string[]> $params     Callback params
     * @param class-string[]             $middleware Array of middleware class-name
     */
    private function trigger(callable $callable, array $params, array $middleware = []): void
    {
        $this->trigger = [
            'callable'      => $callable,
            'params'        => $params,
            'middleware'    => $middleware,
        ];
    }

    /**
     * Dispatch routes and setup trigger.
     *
     * @param string $basePath             Base Path
     * @param bool   $caseMatters          Case-sensitive matters
     * @param bool   $trailingSlashMatters Trailing slash matters
     * @param bool   $multiMatch           Return multi route
     */
    private function dispatch(
        string $basePath = '',
        bool $caseMatters = false,
        bool $trailingSlashMatters = false,
        bool $multiMatch = false
    ): void {

        $basePath        = rtrim($basePath, '/');
        $parsedUrl       = parse_url($this->request->getUrl());
        $path            = $this->resolvePath($parsedUrl, $basePath, $trailingSlashMatters);
        $method          = $this->request->getMethod();
        $pathMatchFound  = false;
        $routeMatchFound = false;

        foreach ($this->routes as $route) {
            $expression         = $route['expression'];
            $originalExpression = $expression;
            $expression         = $this->makeRoutePatterns($expression, $route['patterns'] ?? []);

            // Add basepath to matching string
            if ($basePath !== '' && $basePath !== '/') {
                $expression = "({$basePath}){$expression}";
            }

            // Check path match
            if (preg_match("#^{$expression}$#" . ($caseMatters ? '' : 'i') . 'u', $path, $matches)) {
                $pathMatchFound = true;

                // Cast allowed method to array if it's not one already, then run through all methods
                foreach ((array) $route['method'] as $allowedMethod) {
                    // Check method match
                    if (strtolower($method) !== strtolower($allowedMethod)) {
                        continue;
                    }

                    $parameters = $this->resolveNamedParameters($matches);

                    $this->trigger(
                        callable: $this->found,
                        params: [$route['function'], $parameters],
                        middleware: $route['middleware'] ?? []
                    );
                    $this->current               = $route;
                    $this->current['expression'] = "^{$originalExpression}$";
                    $routeMatchFound             = true;
                    break;
                }
            }

            // Break the loop if the first found route is a match
            if ($routeMatchFound && false === $multiMatch) {
                    break;
            }
        }

        // No matching route was found
        if (false === $routeMatchFound) {
            if ($pathMatchFound && $this->methodNotAllowed) {
                $this->trigger($this->methodNotAllowed, [$path, $method]);
            } elseif (false === $pathMatchFound && $this->notFound) {
                $this->trigger($this->notFound, [$path]);
            }
        }
    }

    /**
     * Resolve path to sanitize trailing slash.
     *
     * @param false|array<string, int|string> $parsedUrl
     * @param string                          $basePath
     * @param bool                            $trailingSlashMatters
     * @return string
     */
    private function resolvePath(array|false $parsedUrl, string $basePath, bool $trailingSlashMatters): string
    {
        $parsedPath = $parsedUrl['path'] ?? null;

        return match (true) {
            null === $parsedPath           => '/',
            $trailingSlashMatters         => $parsedPath,
            "{$basePath}/" !== $parsedPath => rtrim($parsedPath, '/'),
            default                         => $parsedPath,
        };
    }

    /**
     * Parse expression with costume pattern.
     *
     * @param array<string, string> $pattern
     */
    private function makeRoutePatterns(string $expression, array $pattern): string
    {
        if ([] === $pattern) {
            return $expression;
        }

        return Router::mapPatterns($expression, $pattern);
    }

    /**
     * Resolve matches from preg_match path.
     *
     * @param string[] $matches
     * @return array<string|int, string>
     */
    private function resolveNamedParameters(array $matches): array
    {
        $namedMatches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        if (empty($namedMatches)) {
            array_shift($matches);
            $cleanMatches = $matches;
        } else {
            $cleanMatches = array_filter($namedMatches, static fn (string $key): bool => false === is_numeric($key), ARRAY_FILTER_USE_KEY);
        }

        return $cleanMatches;
    }
}
