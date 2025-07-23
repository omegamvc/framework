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

namespace Tests\Support;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Support\Vite;

use function dirname;

/**
 * Test suite for the Vite asset manager.
 *
 * This class covers unit tests for the Vite class, which provides functionality
 * to resolve and load frontend asset paths from a Vite manifest or hot module replacement (HMR) server.
 *
 * Tests include:
 * - Resolving individual and multiple assets via the manifest.
 * - Detecting and using the HMR server when available.
 * - Validating cache behavior.
 * - Invoking the class directly as a callable for asset resolution.
 * - Verifying the generated HMR client script.
 *
 * Each test uses specific fixtures to simulate manifest and HMR environments.
 * The test environment is reset between tests to avoid side effects.
 *
 * @category  Omega\Tests
 * @package   Support
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Vite::class)]
class ViteTest extends TestCase
{
    /**
     * Clean up the test environment after each test.
     *
     * This method flushes and resets the application container
     * to ensure a clean state between tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Vite::flush();
    }

    /**
     * Test it can get file resource name.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetFileResourceName(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/manifest/public', 'build/');

        $file = $asset->get('resources/css/app.css');

        $this->assertEquals('build/assets/app-4ed993c7.js', $file);
    }

    /**
     * Test it can get file resource names.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetFileResourceNames(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/manifest/public', 'build/');

        $files = $asset->gets([
            'resources/css/app.css',
            'resources/js/app.js',
        ]);

        $this->assertEquals([
            'resources/css/app.css' => 'build/assets/app-4ed993c7.js',
            'resources/js/app.js'   => 'build/assets/app-0d91dc04.js',
        ], $files);
    }

    /**
     * Test it can check running hrm exists.
     *
     * @return void
     */
    public function testItCanCheckRunningHRMExist(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/hot/public', 'build/');

        $this->assertTrue($asset->isRunningHRM());
    }

    /**
     * Test it can check running hrm doest exists.
     *
     * @return void
     */
    public function testItCanCheckRunningHRMDoestExist(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/manifest/public', 'build/');

        $this->assertFalse($asset->isRunningHRM());
    }

    /**
     * Test it can get hot file resource name.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetHotFileResourceName(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/hot/public', 'build/');

        $file = $asset->get('resources/css/app.css');

        $this->assertEquals('http://[::1]:5173/resources/css/app.css', $file);
    }

    /**
     * Test it can get hot file resource names.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetHotFileResourceNames(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/hot/public', 'build/');

        $files = $asset->gets([
            'resources/css/app.css',
            'resources/js/app.js',
        ]);

        $this->assertEquals([
            'resources/css/app.css' => 'http://[::1]:5173/resources/css/app.css',
            'resources/js/app.js'   => 'http://[::1]:5173/resources/js/app.js',
        ], $files);
    }

    /**
     * Test it can use cache.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanUseCache(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/manifest/public', 'build/');
        $asset->get('resources/css/app.css');

        $this->assertCount(1, Vite::$cache);
    }

    /**
     * Test it can get file resources using invoke.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetFileResourcesUsingInvoke(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/manifest/public', 'build/');

        $file = $asset('resources/css/app.css');

        $this->assertEquals('build/assets/app-4ed993c7.js', $file);
    }

    /**
     * Test it can get file resource using invoke.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetFileResourceUsingInvoke(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/manifest/public', 'build/');

        $files = $asset(
            'resources/css/app.css',
            'resources/js/app.js'
        );

        $this->assertEquals([
            'resources/css/app.css' => 'build/assets/app-4ed993c7.js',
            'resources/js/app.js'   => 'build/assets/app-0d91dc04.js',
        ], $files);
    }

    /**
     * Test it can get hot resources using invoke.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetHotResourcesUsingInvoke(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/hot/public', 'build/');

        $file = $asset('resources/css/app.css');

        $this->assertEquals('http://[::1]:5173/resources/css/app.css', $file);
    }

    /**
     * Test it can get hot resource using invoke.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetHotResourceUsingInvoke(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/hot/public', 'build/');

        $files = $asset(
            'resources/css/app.css',
            'resources/js/app.js'
        );

        $this->assertEquals([
            'resources/css/app.css' => 'http://[::1]:5173/resources/css/app.css',
            'resources/js/app.js'   => 'http://[::1]:5173/resources/js/app.js',
        ], $files);
    }

    /**
     * Test it can get hot url.
     *
     * @return void
     */
    public function testItCanGetHotUrl(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/hot/public', 'build/');

        $this->assertEquals(
            'http://[::1]:5173/',
            $asset->getHmrUrl()
        );
    }

    /**
     * Test it can change hmr script.
     *
     * @return void
     */
    public function testItChangeHmrScript(): void
    {
        $asset = new Vite(dirname(__DIR__) . '/fixtures/support/hot/public', 'build/');

        $this->assertEquals(
            '<script type="module" src="http://[::1]:5173/@vite/client"></script>',
            $asset->getHmrScript()
        );
    }
}
