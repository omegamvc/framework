<?php

/**
 * Part of Omega CMS - Support Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovanni. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Support\Facade\Facades;

/*
 * @use
 */
use Omega\Support\Facade\AbstractFacade;

/**
 * Class Session.
 *
 * The `Session` class serves as a facade for accessing the view component
 * within the application. By extending the `AbstractFacade`, it provides
 * a static interface for interacting with the underlying view functionality
 * registered in the application container.
 *
 * This class implements the `getFacadeAccessor` method, which returns
 * the key used to resolve the underlying view instance. This allows
 * for a clean and straightforward way to access view-related features
 * without needing to instantiate the underlying components directly.
 *
 * @category    Omega
 * @package     Support
 * @subpackage  Facade\Facades
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 *
 * @method static static flush()                                 Remove all session values.
 * @method static static forget(string $key)                     Remove a single session value.
 * @method static mixed  get(string $key, mixed $default = null) Get a session value,
 * @method static bool   has(string $key)                        Check f a session value exists.
 * @method static static put(string $key, mixed $value)          Store a value in the session.
 */
class Session extends AbstractFacade
{
    /**
     * Get the facade accessor.
     *
     * This method must be implemented by subclasses to return the key used to resolve
     * the underlying instance from the application container.
     *
     * @return string Return the key used to access the underlying instance.
     */
    public static function getFacadeAccessor(): string
    {
        return 'session';
    }
}
