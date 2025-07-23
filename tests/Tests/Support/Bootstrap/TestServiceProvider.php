<?php

/**
 * Part of Omega - Tests\Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Support\Bootstrap;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Container\Provider\AbstractServiceProvider;

/**
 * Test service provider for dependency injection container testing.
 *
 * This mock provider is used to verify the behavior of service registration
 * within the application's container. It retrieves a "ping" value from the
 * container and rebinds it, ensuring service bindings work as expected.
 *
 * @category   Omega\Tests
 * @package    Support
 * @subpackage Bootstrap
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
class TestServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     *
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException   If the "ping" service is not found.
     */
    public function register(): void
    {
        $ping = $this->app->get('ping');
        $this->app->set('ping', $ping);
    }
}
