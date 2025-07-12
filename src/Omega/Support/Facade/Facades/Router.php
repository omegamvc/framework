<?php

/**
 * Part of Omega - Support Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facade\Facades;

use Omega\Support\Facade\AbstractFacade;
use Omega\Routing\Route;

/**
 * Class Router.
 *
 * The `Router` class serves as a facade for accessing the view component
 * within the application. By extending the `AbstractFacade`, it provides
 * a static interface for interacting with the underlying view functionality
 * registered in the application container.
 *
 * This class implements the `getFacadeAccessor` method, which returns
 * the key used to resolve the underlying view instance. This allows
 * for a clean and straightforward way to access view-related features
 * without needing to instantiate the underlying components directly.
 *
 * @category   Omega
 * @package    Facade
 * @subpackage Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 *
 * @method static mixed dispatch()
 * @method static void errorHandler(int $code, mixed $handler)
 * @method static Route get(string $path, mixed $handler, ?string $name = null)
 * @method static Route post(string $path, mixed $handler, ?string $name = null)
 * @method static Route|null getCurrent()
 * @method static array getParameters()
 * @method static string route(string $name, array $parameters = [])
 */
class Router extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor(): string
    {
        return 'router';
    }
}
