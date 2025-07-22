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

use Omega\Application\Application;
use Omega\Support\Facades\Facade;

/**
 * Bootstrap class responsible for binding the application instance to the facade system.
 *
 * This ensures that all facades have access to the underlying application container
 * and can resolve services dynamically at runtime.
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
class RegisterFacades
{
    /**
     * Bootstrap the application by registering the facade base.
     *
     * @param Application $app The application instance.
     * @return void
     */
    public function bootstrap(Application $app): void
    {
        Facade::setFacadeBase($app);
    }
}
