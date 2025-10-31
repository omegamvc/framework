<?php

/**
 * Part of Omega - RateLimiter Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\RateLimiter;

use Omega\Cache\CacheFactory;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Middleware\ThrottleMiddleware;

/**
 * Service provider responsible for registering the RateLimiter and ThrottleMiddleware
 * into the application container.
 *
 * This provider sets up the RateLimiterFactory with a cache instance and binds
 * the ThrottleMiddleware using a fixed window rate limiter.
 *
 * @category  Omega
 * @package   RateLimiter
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class RateLimiterServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     *
     * Registers the rate limiter resolver and the throttle middleware.
     *
     * @throws InvalidDefinitionException If the service definition is invalid.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the cache service is not found.
     */
    public function boot(): void
    {
        $this->registerRateLimiterResolver();
        $this->registerThrottleMiddleware();
    }

    /**
     * Register the RateLimiterFactory in the application container.
     *
     * Retrieves the cache service from the container, creates a RateLimiterFactory
     * using it, and binds the factory instance for later use.
     *
     * @return void
     * @throws InvalidDefinitionException If the service definition is invalid.
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If the cache service is not found.
     */
    protected function registerRateLimiterResolver(): void
    {
        /** @var CacheFactory $cache */
        $cache   = $this->app->get('cache');
        $rate    = new RateLimiterFactory($cache);

        $this->app->set(RateLimiterFactory::class, fn () => $rate);
    }

    /**
     * Register the ThrottleMiddleware in the application container.
     *
     * Retrieves the RateLimiterFactory from the container and creates a
     * ThrottleMiddleware instance using a fixed window rate limiter with
     * a limit of 60 requests per 60 seconds.
     *
     * @return void
     */
    protected function registerThrottleMiddleware(): void
    {
        $rate = $this->app[RateLimiterFactory::class];
        $this->app->set(ThrottleMiddleware::class, fn() => new ThrottleMiddleware(
            limiter: $rate->createFixedWindow(
                limit: 60,
                windowSeconds: 60,
            )
        ));
    }
}
