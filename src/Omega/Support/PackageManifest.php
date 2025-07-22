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

use function array_filter;
use function array_key_exists;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function var_export;

use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

/**
 * Handles reading, caching, and building a manifest of packages and their configuration
 * from the Composer-installed packages. This class is used to retrieve information such
 * as service providers declared in each package's extra section under the "savanna" key.
 *
 * @category  Omega
 * @package   Support
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class PackageManifest
{
    /**
     * Cached package manifest.
     *
     * Format:
     * ```php
     * [
     *     "package/name" => [
     *         "providers" => [
     *             0 => "Vendor\\Package\\ServiceProvider"
     *         ]
     *     ],
     *     // other
     * ]
     * ```
     *
     * @var array<string, array<string, array<int, string>>>|null
     */
    public ?array $packageManifest = null;

    /**
     * PackageManifest constructor.
     *
     * @param string      $basePath             The root path of the application.
     * @param string      $applicationCachePath The path where the package manifest cache is stored.
     * @param string|null $vendorPath           Optional custom vendor directory path. Defaults to '/vendor/composer/'.
     * @return void
     */
    public function __construct(
        private readonly string $basePath,
        private readonly string $applicationCachePath,
        private ?string $vendorPath = null,
    ) {
        $this->vendorPath ??= DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the list of service providers defined in the cached package manifest.
     *
     * @return string[] Array of fully qualified class names of service providers.
     */
    public function providers(): array
    {
        return $this->config('providers');
    }

    /**
     * Get the list of values defined for a specific key (e.g., "providers")
     * from all packages in the manifest.
     *
     * @param string $key The manifest key to retrieve (e.g., "providers").
     * @return string[]   The combined array of all values found under the specified key.
     */
    protected function config(string $key): array
    {
        $manifest = $this->getPackageManifest();
        $result   = [];

        foreach ($manifest as $configuration) {
            if (array_key_exists($key, $configuration)) {
                $values = (array) $configuration[$key];
                foreach ($values as $value) {
                    if (false === empty($value)) {
                        $result[] = $value;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve the cached package manifest, building it if it doesn't exist yet.
     *
     * @return array<string, array<string, array<int, string>>> The cached package data.
     */
    protected function getPackageManifest(): array
    {
        if ($this->packageManifest) {
            return $this->packageManifest;
        }

        if (false === file_exists($this->applicationCachePath . 'packages.php')) {
            $this->build();
        }

        return $this->packageManifest = require $this->applicationCachePath . 'packages.php';
    }

    /**
     * Build the package manifest by scanning Composer's installed.json
     * for packages with an "extra.savanna" section, then writing the data
     * to a PHP cache file.
     *
     * @return void
     */
    public function build(): void
    {
        $packages = [];
        $provider = [];

        // vendor\composer\installed.json
        if (file_exists($file = $this->basePath . $this->vendorPath . 'installed.json')) {
            $installed = file_get_contents($file);
            $installed = json_decode($installed, true);

            $packages = $installed['packages'] ?? [];
        }

        foreach ($packages as $package) {
            if (isset($package['extra']['omegamvc'])) {
                $provider[$package['name']] = $package['extra']['omegamvc'];
            }
        }

        array_filter($provider);

        file_put_contents($this->applicationCachePath . 'packages.php', '<?php return ' . var_export($provider, true) . ';' . PHP_EOL);
    }
}
