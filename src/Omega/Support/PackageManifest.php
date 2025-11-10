<?php

declare(strict_types=1);

namespace Omega\Support;

use function array_filter;
use function array_key_exists;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function var_export;

use const PHP_EOL;

final class PackageManifest
{
    /**
     * Cached package manifest.
     *
     * @var array<string, array<string, array<int, string>>>|null
     */
    public ?array $packageManifest = null;

    public function __construct(
        private readonly string $basePath,
        private readonly string $applicationCachePath,
        private ?string         $vendorPath = null,
    ) {
        $this->vendorPath ??= DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get provider in cache package manifest.
     *
     * @return string[]
     */
    public function providers(): array
    {
        return $this->config('providers');
    }

    /**
     * Get array of provider.
     *
     * @return string[]
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
     * Get cached package manifest has been build.
     *
     * @return array<string, array<string, array<int, string>>>
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
     * Build cache package manifest from composer installed package.
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

        file_put_contents(
            $this->applicationCachePath
            . 'packages.php',
            '<?php return '
            . var_export($provider, true)
            . ';'
            . PHP_EOL
        );
    }
}
