<?php

/**
 * Part of Omega - Tests\Support\Bootstrap Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Support\Bootstrap;

use Exception;
use Omega\Application\Application;
use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Support\Bootstrap\ConfigProviders;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Class ConfigProvidersTest
 *
 * This test suite verifies that the ConfigProviders bootstrapper correctly loads
 * configuration values into the application container. It tests two scenarios:
 *
 * 1. Loading configuration directly from configuration files when no cache is present.
 * 2. Loading configuration from a pre-generated cache file when available.
 *
 * These tests ensure that the application's configuration system behaves consistently
 * and that configuration values are properly accessible through the container once
 * bootstrapped.
 *
 * @category   Tests
 * @package    Support
 * @subpackage Bootstrap
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Application::class)]
#[CoversClass(ConfigProviders::class)]
class ConfigProvidersTest extends TestCase
{
    /**
     * Test it can load config from file.
     *
     * @return void
     * @throws NotFoundException If a dependency required by the Application cannot be found
     * @throws DependencyException If the Application cannot resolve a required dependency
     * @throws InvalidDefinitionException If a definition registered in the container is invalid
     * @throws Exception if a generic error occurred
     */
    public function testItCanLoadConfigFromFile(): void
    {
        $app = new Application(basePath: __DIR__);
        $app->set('path.config', slash(path: __DIR__ . '/fixtures/config/'));

        new ConfigProviders()->bootstrap($app);
        $config = $app->get('config');

        $this->assertEquals('prod', $config->get('environment'));

        $app->flush();
    }

    /**
     * Test it can load config from cache.
     *
     * Assume this test is boostrap application.
     *
     * @return void
     * @throws NotFoundException If a dependency required by the Application cannot be found
     * @throws DependencyException If the Application cannot resolve a required dependency
     * @throws InvalidDefinitionException If a definition registered in the container is invalid
     * @throws Exception if a generic error occurred
     */
    public function testItCanLoadConfigFromCache(): void
    {
        $app = new Application(basePath: __DIR__ . '/fixtures');

        new ConfigProviders()->bootstrap($app);
        $config = $app->get('config');

        $this->assertEquals('prod', $config->get('environment'));

        $app->flush();
    }
}
