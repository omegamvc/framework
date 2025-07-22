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
 * Bootstrap class responsible for registering service providers into the application container.
 *
 * This class delegates to the application's internal logic to automatically register all
 * configured or discovered service providers, ensuring that they are available before
 * the application is fully bootstrapped.
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
class RegisterProviders
{
    /**
     * Bootstrap the application by registering all service providers.
     *
     * @param Application $app The application instance.
     * @return void
     *
     * @throws DependencyException If a dependency injection error occurs during registration.
     * @throws NotFoundException If a provider cannot be found in the container.
     */
    public function bootstrap(Application $app): void
    {
        $app->registerProvider();
    }
}
