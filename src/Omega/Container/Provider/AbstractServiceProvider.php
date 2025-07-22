<?php

/**
 * Part of Omega - Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Container\Provider;

use Omega\Container\Exceptions\DirectoryCreationException;
use Omega\Container\Exceptions\FileCopyException;
use Omega\Application\Application;

use function array_key_exists;
use function array_merge;
use function closedir;
use function copy;
use function file_exists;
use function is_dir;
use function mkdir;
use function opendir;
use function pathinfo;
use function readdir;

use const PATHINFO_DIRNAME;

/**
 * Abstract base class for service providers within the application container.
 *
 * This class serves as a foundational blueprint for all service providers,
 * facilitating the registration and bootstrapping of services in the application.
 * It manages shared modules and provides utility methods for importing files and directories,
 * enabling modular and extensible service management.
 *
 * Concrete service providers should extend this class and implement the `register()` and `boot()`
 * methods to bind services into the container and perform any boot-time initialization.
 *
 * @category   Omega
 * @package    Container
 * @subpackage Provider
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
abstract class AbstractServiceProvider
{
    /**
     * The current application instance.
     *
     * This instance is used to access the application container and other services
     * during registration and boot phases.
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Array of classes to be registered by the service provider.
     *
     * This typically contains mappings of service names to class implementations
     * or other identifiers used during registration.
     *
     * @var array<int|string, class-string>
     */
    protected array $register = [];

    /**
     * Shared modules to import from vendor packages, grouped by tags.
     *
     * Each entry holds an associative array of module paths keyed by module name,
     * allowing organized management of external dependencies or reusable components.
     *
     * @var array<string, array<string, string>>
     */
    protected static array $modules = [];

    /**
     * Create a new service provider instance.
     *
     * @param Application $app The application instance to which this provider belongs.
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Bootstrap any application services.
     *
     * This method is called after all service providers have been registered,
     * allowing for initialization or configuration that depends on other services.
     *
     * @return void
     */
    public function boot(): void
    {
    }

    /**
     * Register bindings or services into the application container.
     *
     * This method is called before the application boots and is where services
     * should be bound to the container.
     *
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * Import a specific file to the application.
     *
     * Copies a file from the source path to the destination path.
     * Creates the destination directory if it does not exist.
     *
     * @param string $from Source file path.
     * @param string $to Destination file path.
     * @param bool $overwrite Whether to overwrite the destination file if it exists.
     * @return bool Returns true on successful copy, false if the file exists and overwrite is false.
     * @throws DirectoryCreationException If the destination directory cannot be created.
     * @throws FileCopyException If the file copy operation fails.
     */
    public static function importFile(string $from, string $to, bool $overwrite = false): bool
    {
        $exists = file_exists($to);
        if (($exists && $overwrite) || !$exists) {
            $path = pathinfo($to, PATHINFO_DIRNAME);
            if (!file_exists($path)) {
                if (!mkdir($path, 0755, true) && !is_dir($path)) {
                    throw new DirectoryCreationException("Failed to create directory: $path");
                }
            }

            if (!@copy($from, $to)) {
                throw new FileCopyException("Failed to copy file from $from to $to");
            }

            return true;
        }

        return false;
    }

    /**
     * Import a directory to the application.
     *
     * Recursively copies the contents of the source directory to the destination directory.
     * Creates the destination directory if it does not exist.
     *
     * @param string $from Source directory path.
     * @param string $to Destination directory path.
     * @param bool $overwrite Whether to overwrite existing files in the destination.
     * @return bool Returns true on successful import.
     * @throws DirectoryCreationException If any destination directory cannot be created.
     * @throws FileCopyException If any file copy operation fails or if the source directory cannot be opened.
     */
    public static function importDir(string $from, string $to, bool $overwrite = false): bool
    {
        $dir = opendir($from);
        if ($dir === false) {
            throw new FileCopyException("Failed to open directory: $from");
        }

        if (!file_exists($to)) {
            if (!mkdir($to, 0755, true) && !is_dir($to)) {
                closedir($dir);
                throw new DirectoryCreationException("Failed to create directory: $to");
            }
        }

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $src = $from . '/' . $file;
            $dst = $to . '/' . $file;

            if (is_dir($src)) {
                static::importDir($src, $dst, $overwrite);
            } else {
                static::importFile($src, $dst, $overwrite);
            }
        }

        closedir($dir);

        return true;
    }

    /**
     * Register modules to be shared by the service provider.
     *
     * Modules are grouped by tags to allow selective importing or usage.
     *
     * @param array<string, string> $path Associative array of module name => path.
     * @param string                $tag  Optional tag to categorize modules.
     * @return void
     */
    public static function export(array $path, string $tag = ''): void
    {
        if (false === array_key_exists($tag, static::$modules)) {
            static::$modules[$tag] = [];
        }

        static::$modules[$tag] = array_merge(static::$modules[$tag], $path);
    }

    /**
     * Retrieve all registered shared modules.
     *
     * @return array<string, array<string, string>> Array of modules grouped by tags.
     */
    public static function getModules(): array
    {
        return static::$modules;
    }

    /**
     * Clear all registered shared modules.
     *
     * @return void
     */
    public static function flushModule(): void
    {
        static::$modules = [];
    }
}
