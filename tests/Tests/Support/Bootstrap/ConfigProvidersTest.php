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
use Omega\Application\Application;
use Omega\Support\Bootstrap\ConfigProviders;
use Omega\Support\Facades\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function dirname;

/**
 * Class ConfigProvidersTest
 *
 * This test class verifies the behavior of the ConfigProviders bootstrapper.
 * It ensures that configuration values are properly loaded from different sources:
 * - Default configuration
 * - Configuration files
 * - Cached configuration
 *
 * Each test method initializes an Application instance, applies the configuration
 * bootstrapper, and asserts that the expected configuration value (e.g., ENVIRONMENT)
 * is correctly set.
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
#[CoversClass(Application::class)]
#[CoversClass(Config::class)]
#[CoversClass(ConfigProviders::class)]
class ConfigProvidersTest extends TestCase
{
    /**
     * Test it can load config from default.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanLoadConfigFromDefault(): void
    {
        $app = new Application('/');

        (new ConfigProviders())->bootstrap($app);
        /** @var Config $config*/
        $config = $app->get('config');

        $this->assertEquals('dev', $config->get('ENVIRONMENT'));

        $app->flush();
    }

    /**
     * Test it can load config from file.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanLoadConfigFromFile(): void
    {
        $app = new Application(dirname(__DIR__, 2));

        $app->setConfigPath('/fixtures/support/bootstrap/app/config/');

        (new ConfigProviders())->bootstrap($app);
        /** @var Config $config */
        $config = $app->get('config');

        $this->assertEquals('test', $config->get('ENVIRONMENT'));

        $app->flush();
    }

    /**
     * Assume this test is boostrap application.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanLoadConfigFromCache(): void
    {
        $app = new Application(dirname(__DIR__, 2) . '/fixtures/support/bootstrap/app2');

        (new ConfigProviders())->bootstrap($app);
        /** @var Config $config */
        $config = $app->get('config');

        $this->assertEquals('prod', $config->get('ENVIRONMENT'));

        $app->flush();
    }
}
