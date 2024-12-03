<?php

/**
 * Part of Omega CMS - Container Package.
 * php version 8.3
 *
 * @link      https://omegacms.github.io
 * @author    Adriano Giovannini <omegacms@outlook.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Container\ServiceProvider;

use Omega\Application\Application;

/**
 * ServiceProviderInterface defines a contract for binding services to an
 * application container within the Omega CMS framework. Implementations of
 * this interface will provide the logic to register services or dependencies
 * into the application's service container.
 *
 * - `bind(Application $application): void`
 *   - This method is responsible for binding services or components to the provided `Application` instance.
 *   - The `Application` parameter represents the main application container to which services are bound.
 *   - The method does not return a value and is expected to perform its operations internally.
 *
 * @category   Omega
 * @package    Container
 * @subpackage ServiceProvider
 * @link       https://omegacms.github.io
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface ServiceProviderInterface
{
    /**
     * Registers or binds services into the application container.
     *
     * @param Application $application Holds the main application container to which services are bound.
     *
     * @return void This method does not return a value.
     */
    public function bind(Application $application): void;
}
