<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support;

use function array_map;
use function glob;
use function is_array;
use function is_string;
use function ltrim;
use function preg_quote;
use function preg_replace;
use function rtrim;
use function str_contains;
use function str_replace;

use const DIRECTORY_SEPARATOR;

/**
 * Path utility class for resolving and normalizing filesystem paths
 * relative to a base project directory.
 *
 * This class supports dot notation paths and can return single paths
 * or arrays of paths based on file names or glob patterns.
 *
 * Example usage:
 * ```php
 * Path::init('/var/www/project');
 *
 * // Get directory path with trailing slash
 * echo Path::getPath('app.Model'); // Outputs: /var/www/project/app/Model/
 *
 * // Get file path inside directory
 * echo Path::getPath('app.Model', 'User.php'); // Outputs: /var/www/project/app/Model/User.php
 *
 * // Get multiple file paths by array
 * print_r(Path::getPaths('app.Model', ['User.php', 'Post.php']));
 *
 * // Get multiple file paths by glob pattern
 * print_r(Path::getPaths('app.Model', '*.php'));
 *
 * @category  Omega
 * @package   Support
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Path
{
    /**
     * Base path of the project, used as prefix for all resolved paths.
     *
     * @var string
     */
    private static string $basePath = '';

    /**
     * Initialize the base path of the project.
     *
     * Sets the root directory from which all dot notation paths will be resolved.
     * If null or empty, defaults to empty string.
     *
     * @param string|null $basePath Absolute path to project root
     * @return void
     */
    public static function init(?string $basePath = null): void
    {
        self::$basePath = rtrim($basePath ?? '', DIRECTORY_SEPARATOR);
    }

    /**
     * Resolve a single path from dot notation and optional file name.
     *
     * Converts dot notation (e.g., 'app.Model') into a full path based on
     * the initialized base path.
     *
     * If no file is given, returns the directory path with trailing slash.
     * If file is provided, returns the full path to the file (no trailing slash).
     *
     * @param string $dotPath Dot notation path representing a directory (e.g., 'app.Model')
     * @param string|null $file Optional file name inside the directory (e.g., 'User.php')
     * @return string Resolved and normalized full path
     */
    public static function getPath(string $dotPath, ?string $file = null): string
    {
        $dirPath = self::resolveDotPath($dotPath);

        if ($file === null) {
            // Directory path, ensure trailing slash
            return rtrim($dirPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }

        // File path, no trailing slash
        return rtrim($dirPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);
    }

    /**
     * Resolve multiple paths from dot notation and a file name, array of files, or glob pattern.
     *
     * Accepts a single file name, an array of file names, or a glob pattern (with '*').
     * Returns an array of resolved, normalized paths matching the criteria.
     *
     * Examples:
     * ```php
     * // Multiple specific files
     * Path::getPaths('app.Model', ['User.php', 'Post.php']);
     *
     * // Glob pattern matching all PHP files
     * Path::getPaths('app.Model', '*.php');
     * ```
     *
     * @param string $dotPath Dot notation path representing a directory
     * @param string|array $files File name, array of file names, or glob pattern
     * @return string[] Array of resolved and normalized paths
     */
    public static function getPaths(string $dotPath, string|array $files): array
    {
        $dirPath = self::resolveDotPath($dotPath);
        $paths = [];

        // If string contains glob '*', use glob function
        if (is_string($files) && str_contains($files, '*')) {
            $globResults = glob(rtrim($dirPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $files);
            if ($globResults === false) {
                return [];
            }
            return array_map(fn($p) => self::normalizePath($p), $globResults);
        }

        // Otherwise, single file or array of files
        $fileList = is_array($files) ? $files : [$files];
        foreach ($fileList as $file) {
            $fullPath = rtrim($dirPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR);
            $paths[] = self::normalizePath($fullPath);
        }

        return $paths;
    }

    /**
     * Convert dot notation string to a normalized full directory path.
     *
     * Replaces dots with directory separators and prefixes base path.
     *
     * @param string $dotPath Dot notation string (e.g., 'app.Model')
     * @return string Normalized absolute path without trailing slash
     */
    private static function resolveDotPath(string $dotPath): string
    {
        $relativePath = str_replace('.', DIRECTORY_SEPARATOR, $dotPath);

        if (self::$basePath === '') {
            return self::normalizePath(ltrim($relativePath, DIRECTORY_SEPARATOR));
        }

        return self::normalizePath(self::$basePath . DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR));
    }

    /**
     * Normalize a filesystem path by removing duplicate slashes
     * and trimming trailing directory separators.
     *
     * @param string $path Path to normalize
     * @return string Normalized path without trailing slash
     */
    private static function normalizePath(string $path): string
    {
        $normalized = preg_replace('#' . preg_quote(DIRECTORY_SEPARATOR) . '+#', DIRECTORY_SEPARATOR, $path);
        return rtrim($normalized, DIRECTORY_SEPARATOR);
    }
}
