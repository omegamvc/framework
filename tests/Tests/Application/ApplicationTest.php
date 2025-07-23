<?php

/**
 * Part of Omega - Tests\Application Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Application;

use DI\DependencyException;
use DI\NotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Http\Request;
use Omega\Application\Application;
use Omega\Config\ConfigRepository;
use Omega\Exceptions\ApplicationNotAvailableException;
use Omega\Http\Exceptions\HttpException;
use Tests\Support\Bootstrap\TestBootstrapProvider;
use Tests\Support\Bootstrap\TestServiceProvider;

use function dirname;

use const DIRECTORY_SEPARATOR;

/**
 * Test suite for the Omega Application class and its related components.
 *
 * This class tests the initialization, configuration loading, environment detection,
 * macro registration, bootstrapping logic, and lifecycle of the Application class.
 * It also covers fallback behavior when the application is not available and ensures
 * correct handling of maintenance mode, termination routines, and deprecated methods.
 *
 *
 * @category  Omega\Tests
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Request::class)]
#[CoversClass(Application::class)]
#[CoversClass(ConfigRepository::class)]
#[CoversClass(ApplicationNotAvailableException::class)]
#[CoversClass(HttpException::class)]
class ApplicationTest extends TestCase
{
    /**
     * Test it throw error.
     *
     * @return void
     */
    public function testItThrowError(): void
    {
        $this->expectException(ApplicationNotAvailableException::class);
        app();
        app()->flush();
    }

    /**
     * Test it throw after flush application.
     *
     * @return void
     */
    public function testItThrowErrorAfterFlushApplication(): void
    {
        $app = new Application('/');
        $app->flush();

        $this->expectException(ApplicationNotAvailableException::class);
        app();
        app()->flush();
    }

    /**
     * Test it can load app.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanLoadApp(): void
    {
        $app = new Application('/');

        $this->assertEquals('/', base_path());

        $app->flush();
    }

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

        $app->loadConfig(new ConfigRepository($app->defaultConfigs()));
        /** @var ConfigRepository $config */
        $config = $app->get('config');

        $this->assertEquals($this->defaultConfigs(), $config->toArray());

        $app->flush();
    }

    /**
     * Test it can load environment.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanLoadEnvironment(): void
    {
        $app = new Application('/');

        $env = $app->defaultConfigs();
        $app->loadConfig(new ConfigRepository($env));
        $this->assertTrue($app->isDev());
        $this->assertFalse($app->isProduction());

        $env['ENVIRONMENT'] = 'test';

        $app->loadConfig(new ConfigRepository($env));
        $this->assertEquals('test', $app->getEnvironment());

        // APP_ENV
        $env['APP_ENV'] = 'dev';

        $app->loadConfig(new ConfigRepository($env));
        $this->assertEquals('dev', $app->getEnvironment());

        $app->flush();
    }

    /**
     * Test it can detect debug mode.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanDetectDebugMode(): void
    {
        $app = new Application('/');

        $env = $app->defaultConfigs();
        $app->loadConfig(new ConfigRepository($env));
        $this->assertFalse($app->isDebugMode());

        $app->flush();
    }

    /**
     * Test it can not duplicate register.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanNotDuplicateRegister(): void
    {
        require_once dirname(__DIR__) . '/Support/Bootstrap/TestServiceProvider.php';

        $app = new Application('/');

        $app->set('ping', 'pong');

        $app->register(TestServiceProvider::class);
        $app->register(TestServiceProvider::class);

        $test = $app->get('ping');

        $this->assertEquals('pong', $test);
    }

    /**
     * Test it can call macro request validate.
     *
     * @return void
     */
    public function testItCanCallMacroRequestValidate(): void
    {
        new Application('/');

        $this->assertTrue(Request::hasMacro('validate'));
    }

    /**
     * Test it can call macro request uploads.
     *
     * @return void
     */
    public function testItCanCallMacroRequestUploads(): void
    {
        new Application('/');

        $this->assertTrue(Request::hasMacro('upload'));
    }

    /**
     * Test it can terminate after application done.
     *
     * @return void
     */
    public function testItCanTerminateAfterApplicationDone(): void
    {
        $app = new Application('/');
        $app->registerTerminate(static function () {
            echo 'terminated.';
        });
        ob_start();
        echo 'application started.';
        echo 'application ended.';
        $app->terminate();
        $out = ob_get_clean();

        $this->assertEquals('application started.application ended.terminated.', $out);
    }

    /**
     * Test it can detect maintenance mode.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanDetectMaintenanceMode(): void
    {
        $app = new Application(dirname(__DIR__));
        $app->loadConfig(new ConfigRepository($app->defaultConfigs()));

        $this->assertFalse($app->isDownMaintenanceMode());

        $app->setStoragePath(DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR);

        $this->assertTrue($app->isDownMaintenanceMode());
    }

    /**
     * Test it can get down.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanGetDown(): void
    {
        $app = new Application(dirname(__DIR__));
        $app->setStoragePath(DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR);

        $this->assertEquals([
            'redirect' => null,
            'retry'    => 15,
            'status'   => 503,
            'template' => null,
        ], $app->getDownData());
    }

    /**
     * Test it can get down default.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanGetDownDefault(): void
    {
        $app = new Application('/');

        $app->loadConfig(new ConfigRepository($app->defaultConfigs()));
        $this->assertEquals([
            'redirect' => null,
            'retry'    => null,
            'status'   => 503,
            'template' => null,
        ], $app->getDownData());
    }

    /**
     * Test it can abort application.
     *
     * @return void
     */
    public function testItCanAbortApplication(): void
    {
        $this->expectException(HttpException::class);
        (new Application(__DIR__))->abort(500);
    }

    /**
     * Test it can bootstrap with.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanBootstrapWith(): void
    {
        $app = new Application(__DIR__);

        ob_start();
        $app->bootstrapWith([
            TestBootstrapProvider::class,
        ]);
        $out = ob_get_clean();

        $this->assertEquals('Tests\Support\Bootstrap\TestBootstrapProvider::bootstrap', $out);
        $this->assertTrue($app->isBootstrapped());
    }

    /**
     * Test it can add call backs before after boot.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanAddCallBacksBeforeAndAfterBoot(): void
    {
        $app = new Application(dirname(__DIR__) . '/fixtures/application/app/');

        $app->bootedCallback(static function () {
            echo 'booted01';
        });
        $app->bootedCallback(static function () {
            echo 'booted02';
        });
        $app->bootingCallback(static function () {
            echo 'booting01';
        });
        $app->bootingCallback(static function () {
            echo 'booting02';
        });

        ob_start();
        $app->bootProvider();
        $out = ob_get_clean();

        $this->assertEquals('booting01booting02booted01booted02', $out);
        $this->assertTrue($app->isBooted());
    }

    /**
     * Test it can add call Immediately if application already booted.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanAddCallImmediatelyIfApplicationAlreadyBooted(): void
    {
        $app = new Application(dirname(__DIR__) . '/fixtures/application/app/');

        $app->bootProvider();

        ob_start();
        $app->bootedCallback(static function () {
            echo 'immediately call';
        });
        $out = ob_get_clean();

        $this->assertTrue($app->isBooted());
        $this->assertEquals('immediately call', $out);
    }

    /**
     * Test it can call deprecated method.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanCallDeprecatedMethod(): void
    {
        $app = new Application(__DIR__);
        $app->loadConfig(new ConfigRepository($app->defaultConfigs()));

        $this->assertEquals($app->getBasePath(), base_path());
        $this->assertEquals($app->getAppPath(), app_path());
        $this->assertEquals($app->getModelPath(), model_path());
        $this->assertEquals($app->getViewPath(), view_path());
        $this->assertEquals($app->getServicesPath(), services_path());
        $this->assertEquals($app->getComponentPath(), component_path());
        $this->assertEquals($app->getCommandPath(), commands_path());
        $this->assertEquals($app->getControllerPath(), controllers_path());
        $this->assertEquals($app->getStoragePath(), storage_path());
        $this->assertEquals($app->getCachePath(), cache_path());
        $this->assertEquals($app->getCompiledViewPath(), compiled_view_path());
        $this->assertEquals($app->getConfigPath(), config_path());
        $this->assertEquals($app->getMiddlewarePath(), middleware_path());
        $this->assertEquals($app->getProviderPath(), provider_path());
        $this->assertEquals($app->getMigrationPath(), migration_path());
        $this->assertEquals($app->getSeederPath(), seeder_path());
        $this->assertEquals($app->getPublicPath(), public_path());
    }

    /**
     * An array of configuration data for test.
     *
     * @return array
     */
    private function defaultConfigs(): array
    {
        return [
            // app config
            'BASEURL'               => '/',
            'time_zone'             => 'UTC',
            'APP_KEY'               => '',
            'ENVIRONMENT'           => 'dev',
            'APP_DEBUG'             => 'false',
            'BCRYPT_ROUNDS'         => 12,
            'CACHE_STORE'           => 'file',

            'COMMAND_PATH'          => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Commands' . DIRECTORY_SEPARATOR,
            'CONTROLLER_PATH'       => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR,
            'MODEL_PATH'            => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR,
            'MIDDLEWARE'            => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Middlewares' . DIRECTORY_SEPARATOR,
            'SERVICE_PROVIDER'      => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Providers' . DIRECTORY_SEPARATOR,
            'CONFIG'                => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR,
            'SERVICES_PATH'         => DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Services' . DIRECTORY_SEPARATOR,
            'VIEW_PATH'             => DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
            'COMPONENT_PATH'        => DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR,
            'STORAGE_PATH'          => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR,
            'CACHE_PATH'            => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR,
            'CACHE_VIEW_PATH'       => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
            'PUBLIC_PATH'           => DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR,
            'MIGRATION_PATH'        => DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR,
            'SEEDER_PATH'           => DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders' . DIRECTORY_SEPARATOR,
            'PROVIDERS'             => [
                // provider class name
            ],

            // db config
            'DB_HOST'               => 'localhost',
            'DB_USER'               => 'root',
            'DB_PASS'               => 'vb65ty4',
            'DB_NAME'               => 'phpmvc',

            // pusher
            'PUSHER_APP_ID'         => '',
            'PUSHER_APP_KEY'        => '',
            'PUSHER_APP_SECRET'     => '',
            'PUSHER_APP_CLUSTER'    => '',

            // redis driver
            'REDIS_HOST'            => '127.0.0.1',
            'REDIS_PASS'            => '',
            'REDIS_PORT'            => 6379,

            // memcached
            'MEMCACHED_HOST'        => '127.0.0.1',
            'MEMCACHED_PASS'        => '',
            'MEMCACHED_PORT'        => 6379,

            // view config
            'VIEW_PATHS' => [
                DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,
            ],
            'VIEW_EXTENSIONS' => [
                '.template.php',
                '.php',
            ],
            'COMPILED_VIEW_PATH' => DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR,
        ];
    }
}
