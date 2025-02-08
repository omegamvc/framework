<?php

/**
 * Part of Omega - Http Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Http\ServiceProvider;

use Omega\Application\Application;
use Omega\Container\Contracts\ServiceProvider\ServiceProviderInterface;
use Omega\Http\Response;

/**
 * Response service provider class.
 *
 * The `ResponseServiceProvider` class is responsible for binding the Response class
 * to the application container in Omega.
 *
 * @category   Omega
 * @package    Http
 * @subpackage ServiceProvider
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class ResponseServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function bind(Application $application): void
    {
        $application->alias('response', function () {
            return new Response();
        });
    }
}
