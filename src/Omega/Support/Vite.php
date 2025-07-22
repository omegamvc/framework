<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

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

use Exception;
use Omega\Collection\Collection;
use Omega\Text\Str;

use function array_key_exists;
use function array_key_first;
use function count;
use function file_exists;
use function file_get_contents;
use function filemtime;
use function is_file;
use function is_null;
use function json_decode;
use function rtrim;
use function sprintf;

/**
 * Class Vite
 *
 * Handles loading and resolving Vite-generated asset files from a manifest or via Hot Module Replacement (HMR).
 *
 * This class provides a seamless way to fetch compiled asset paths (JavaScript, CSS, etc.)
 * during development or production by either using a manifest file or an HMR server.
 *
 * Supports caching for improved performance, and allows switching between hot and static builds.
 *
 * @category  Omega
 * @package   Support
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class Vite
{
    /**
     * The name of the manifest file used to resolve asset entries.
     *
     * Defaults to 'manifest.json'.
     *
     * @var string
     */
    private string $manifestName;

    /**
     * The last known modification time of the manifest file.
     *
     * This value is updated when the manifest is (re)loaded.
     *
     * @var int
     */
    private int $cacheTime = 0;

    /**
     * A static in-memory cache of loaded manifest files.
     *
     * Format:
     * ```php
     *  [
     *      'path/to/manifest.json' => [
     *          'entry.js' => ['file' => 'entry.abc123.js'],
     *          // other
     *      ],
     *      // other
     *  ]
     * ```
     *
     * @var array<string, array<string, array<string, string>>>
     */
    public static array $cache = [];

    /**
     * The resolved hot-reload URL from the 'hot' file, if any.
     *
     * Used when Vite is running in development mode with HMR enabled.
     *
     * @var string|null
     */
    public static ?string $hot = null;

    /**
     * Vite constructor.
     *
     * @param string $publicPath The path to the public directory where assets are exposed.
     * @param string $buildPath The subdirectory where Vite places the compiled assets.
     */
    public function __construct(private readonly string $publicPath, private readonly string $buildPath)
    {
        $this->manifestName = 'manifest.json';
    }

    /**
     * Resolve asset(s) from the manifest or hot module server using entry point(s).
     *
     * @param string ...$entryPoints One or more Vite entry points.
     * @return array<string, string>|string A single file path if one entry is passed, or an associative array if multiple.
     * @throws Exception If resources are missing or the manifest is invalid.
     */
    public function __invoke(string ...$entryPoints): array|string
    {
        $resource = $this->gets($entryPoints);
        $first    = array_key_first($resource);

        return 1 === count($resource) ? $resource[$first] : $resource;
    }

    /**
     * Set a custom manifest filename.
     *
     * @param string $manifestName The name of the manifest file (e.g., 'custom-manifest.json').
     * @return $this
     */
    public function manifestName(string $manifestName): self
    {
        $this->manifestName = $manifestName;

        return $this;
    }

    /**
     * Flush the cached manifest data and reset the hot reload URL.
     *
     * @return void
     */
    public static function flush(): void
    {
        static::$cache = [];
        static::$hot   = null;
    }

    /**
     * Get the full file path to the manifest.
     *
     * @return string
     * @throws Exception If the manifest file does not exist.
     */
    public function manifest(): string
    {
        if (file_exists($fileName = "{$this->publicPath}/{$this->buildPath}/{$this->manifestName}")) {
            return $fileName;
        }

        throw new Exception(sprintf("Manifest file not found %s", $fileName));
    }

    /**
     * Load and parse the manifest file, using the in-memory cache if available.
     *
     * @return array<string, array<string, string>> The decoded manifest content.
     * @throws Exception If the manifest is missing or contains invalid JSON.
     */
    public function loader(): array
    {
        $fileName = $this->manifest();

        if (array_key_exists($fileName, static::$cache)) {
            return static::$cache[$fileName];
        }

        $this->cacheTime = $this->manifestTime();
        $load             = file_get_contents($fileName);
        $json             = json_decode($load, true);

        if (false === $json) {
            throw new Exception('Manifest doest support');
        }

        return static::$cache[$fileName] = $json;
    }

    /**
     * Retrieve the file path for a specific resource from the manifest.
     *
     * @param string $resourceName The name of the resource (e.g., 'app.js').
     * @return string The resolved asset path.
     * @throws Exception If the resource is not found in the manifest.
     */
    public function getManifest(string $resourceName): string
    {
        $asset = $this->loader();

        if (!array_key_exists($resourceName, $asset)) {
            throw new Exception("Resource file not found {$resourceName}");
        }

        return $this->buildPath . $asset[$resourceName]['file'];
    }

    /**
     * Retrieve multiple file paths from the manifest.
     *
     * @param string[] $resourceNames List of resource names to resolve.
     * @return array<string, string> Associative array of resource names and resolved paths.
     * @throws Exception If the manifest is invalid.
     */
    public function getsManifest(array $resourceNames): array
    {
        $asset = $this->loader();

        $resources = [];
        foreach ($resourceNames as $resource) {
            if (array_key_exists($resource, $asset)) {
                $resources[$resource] = $this->buildPath . $asset[$resource]['file'];
            }
        }

        return $resources;
    }

    /**
     * Get the resource URL, using HMR if running, otherwise from the manifest.
     *
     * @param string $resourceName The name of the resource (e.g., 'main.js').
     * @return string The resolved URL.
     * @throws Exception If the resource is not found or manifest is invalid.
     */
    public function get(string $resourceName): string
    {
        if (!$this->isRunningHRM()) {
            return $this->getManifest($resourceName);
        }

        $hot = $this->getHmrUrl();

        return $hot . $resourceName;
    }

    /**
     * Get multiple resource URLs, using HMR if running, otherwise from the manifest.
     *
     * @param string[] $resourceNames List of resource names.
     * @return array<string, string> Associative array of resolved URLs.
     * @throws Exception If the manifest is invalid.
     */
    public function gets(array $resourceNames): array
    {
        if (!$this->isRunningHRM()) {
            return $this->getsManifest($resourceNames);
        }

        $hot  = $this->getHmrUrl();

        return (new Collection($resourceNames))
            ->assocBy(fn ($asset) => [$asset => $hot . $asset])
            ->toArray()
            ;
    }

    /**
     * Determine if the Vite development server with HMR is running.
     *
     * @return bool True if the 'hot' file exists, indicating HMR is active.
     */
    public function isRunningHRM(): bool
    {
        return is_file("{$this->publicPath}/hot");
    }

    /**
     * Resolve and return the base URL of the HMR server.
     *
     * @return string The base URL, ending with a slash.
     */
    public function getHmrUrl(): string
    {
        if (!is_null(static::$hot)) {
            return static::$hot;
        }

        $hot  = file_get_contents("{$this->publicPath}/hot");
        $hot  = rtrim($hot);
        $dash = Str::endsWith($hot, '/') ? '' : '/';

        return static::$hot = $hot . $dash;
    }

    /**
     * Return the script tag to enable HMR with the Vite client.
     *
     * @return string A full <script> tag with type="module".
     */
    public function getHmrScript(): string
    {
        return '<script type="module" src="' . $this->getHmrUrl() . '@vite/client"></script>';
    }

    /**
     * Get the last known cache time of the manifest file.
     *
     * @return int Unix timestamp of the last manifest read.
     */
    public function cacheTime(): int
    {
        return $this->cacheTime;
    }

    /**
     * Get the last modification time of the manifest file.
     *
     * @return int Unix timestamp.
     * @throws Exception If the manifest file is not found.
     */
    public function manifestTime(): int
    {
        return filemtime($this->manifest());
    }
}
