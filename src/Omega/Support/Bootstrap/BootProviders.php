<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Bootstrap;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Application\Application;

/**
 * Class responsible for booting all registered service providers.
 *
 * This class is used during the application bootstrap process
 * to initialize any providers that have been registered and are
 * ready to be booted.
 *
 * @category   Omega
 * @package    Support
 * @subpackage Bootstrap
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class BootProviders
{
    /**
     * Bootstrap the registered service providers.
     *
     * This method triggers the `bootProvider()` method on the given
     * application instance to initialize all providers.
     *
     * @param Application $app The application instance.
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a provider dependency is not found.
     */
    public function bootstrap(Application $app): void
    {
        $app->bootProvider();
    }
}
