<?php

/**
 * Part of Omega MVC - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Support;

use function implode;
use function ltrim;
use function rtrim;

/**
 * Path class.
 *
 * This class provides utility methods for managing paths within the Omega MVC framework.
 * It allows the initialization of a base path for the project and provides a method to
 * generate full paths by joining the base path with other directory or file names.
 *
 * The base path can be set using the `init()` method and is used globally throughout
 * the application. The `getPath()` method can then be used to generate paths for various
 * resources (e.g., configuration files, database directories) relative to the base path.
 *
 * @category  Omega
 * @package   Support
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
class Path
{
    /**
     * The base path of the project.
     * This is used as the starting point for generating other paths within the project.
     * It is initialized via the `init()` method and is used globally by the `getPath()` method.
     *
     * @var string
     */
    private static string $basePath = '';

    /**
     * Initialize the base path.
     *
     * This method sets the base path for the project. If no path is provided, the default value is an empty string.
     * This base path will be used by the `getPath()` method to build full paths for different resources.
     *
     * Example usage:
     * ```php
     * Path::init('/var/www/project');
     * ```
     *
     * @param string|null $basePath The base path of the project.
     */
    public static function init(?string $basePath = null): void
    {
        self::$basePath = rtrim($basePath ?? '', DIRECTORY_SEPARATOR);
    }

    /**
     * Get the full path by joining the base path with optional subdirectories.
     *
     * This method returns the full path by joining the base path with the specified main directory (`$fullPath`)
     * and an optional subdirectory (`$subPath`). If no arguments are passed, it simply returns the base path.
     * If only `$fullPath` is passed, it will return the full path to that directory. If both `$fullPath` and `$subPath`
     * are provided, it will return the full path to the subdirectory.
     *
     * Example usage:
     * ```php
     * // Assuming base path is initialized as '/var/www/project'
     * Path::init('/var/www/project');
     *
     * // Get the path to the 'database' directory
     * echo Path::getPath('database'); // Output: '/var/www/project/database'
     *
     * // Get the path to the 'database/migrations' subdirectory
     * echo Path::getPath('database', 'migrations'); // Output: '/var/www/project/database/migrations'
     * ```
     *
     * @param string|null $fullPath The main directory name (e.g., 'database', 'config', etc.).
     * @param string|null $subPath  An optional subdirectory.
     * @return string The full path.
     */
    public static function getPath(?string $fullPath = null, ?string $subPath = null): string
    {
        $path = self::$basePath;

        if ($fullPath) {
            $path = self::joinPaths($path, $fullPath);
        }

        if ($subPath) {
            $path = self::joinPaths($path, $subPath);
        }

        return $path;
    }

    /**
     * Join the given paths.
     *
     * This method takes a base path and additional paths as arguments, then joins them together, ensuring
     * proper directory separators between each path component. It trims any extra separators from the start
     * and end of each path before joining them.
     *
     * @param string      $basePath The base path.
     * @param string|null ...$paths Additional paths.
     * @return string The joined path.
     */
    private static function joinPaths(string $basePath, ?string ...$paths): string
    {
        foreach ($paths as $index => $path) {
            if (empty($path)) {
                unset($paths[$index]);
            } else {
                $paths[$index] = DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
            }
        }

        return rtrim($basePath, DIRECTORY_SEPARATOR) . implode('', $paths);
    }
}
