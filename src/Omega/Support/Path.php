<?php

/**
 * Part of Omega MVC - Support Package
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support;

use function implode;
use function rtrim;
use function str_replace;

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
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
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
     * Path::init('/var/www/project/');
     * ```
     *
     * @param string|null $basePath The base path of the project.
     */
    public static function init(?string $basePath = null): void
    {
        self::$basePath = rtrim($basePath ?? '', DIRECTORY_SEPARATOR);
    }

    /**
     * Get the full path by joining the base path with a directory and an optional file.
     *
     * This method returns the full path by combining the base path with the specified logical
     * directory (`$fullPath`) and an optional file name (`$subPath`). The first argument is always
     * treated as a directory path (converted from dot notation to directory separators) and will
     * end with a trailing slash. The second argument, if present, is treated as a file name and
     * appended directly to the resulting path.
     *
     * Example:
     * ```
     * Path::init('/var/www/project');
     *
     * Path::getPath('storage.logs');             // returns '/var/www/project/storage/logs/'
     * Path::getPath('storage.logs', 'app.log');  // returns '/var/www/project/storage/logs/app.log'
     * ```
     *
     * @param string|null $fullPath The directory path in dot notation (e.g., 'storage.logs').
     * @param string|null $file     The file name to append to the directory path.
     * @return string The full absolute path.
     */
    public static function getPath(?string $fullPath = null, ?string $file = null): string
    {
        $path = self::$basePath;

        if ($fullPath) {
            $fullPath = str_replace('.', DIRECTORY_SEPARATOR, $fullPath);
            $path = self::joinPaths($path, $fullPath);
        }

        if ($file) {
            $path = self::joinPaths($path, $file);
        } else {
            // solo se è una cartella, aggiungo lo slash finale
            $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        return $path;
    }

    /**
     * Join multiple path segments into a normalized path string.
     *
     * This method takes any number of path segments and joins them using the system's
     * directory separator. It ensures that there are no duplicate slashes between segments.
     * Leading and trailing slashes are trimmed from all segments except the first,
     * which may retain its leading slash for absolute paths.
     *
     * Examples:
     * ```php
     * joinPaths('/var/www', 'project')        => '/var/www/project'
     * joinPaths('/base/', '/to/', '/file')    => '/base/to/file'
     * joinPaths('path', 'to', 'file.txt')     => 'path/to/file.txt'
     * ```
     *
     * @param string ...$segments One or more path segments to join.
     * @return string The normalized joined path.
     */
    private static function joinPaths(string ...$segments): string
    {
        // Rimuove slash iniziali/finali da ogni segmento tranne il primo
        $segments = array_map(
            static fn($i, $part) => $i === 0
                ? rtrim($part, DIRECTORY_SEPARATOR)
                : trim($part, DIRECTORY_SEPARATOR),
            array_keys($segments),
            $segments
        );

        return implode(DIRECTORY_SEPARATOR, $segments);
    }
}
