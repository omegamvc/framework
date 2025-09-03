<?php /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Support;

use Exception;

use function array_combine;
use function array_diff_key;
use function array_fill;
use function array_fill_keys;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function file_exists;
use function file_get_contents;
use function htmlspecialchars;
use function implode;
use function is_bool;
use function is_int;
use function is_null;
use function json_decode;
use function json_last_error;
use function json_last_error_msg;
use function preg_match;
use function rtrim;
use function str_ends_with;

use const ARRAY_FILTER_USE_BOTH;
use const ENT_QUOTES;
use const JSON_ERROR_NONE;

class Vite
{
    private string $manifestName;

    private int $cacheTime = 0;

    /** @var array<string, array<string, array<string, string>>> */
    public static array $cache = [];

    public static ?string $hot = null;

    public function __construct(
        private readonly string $publicPath,
        private readonly string $buildPath,
    ) {
        $this->manifestName = 'manifest.json';
    }

    /**
     * Get/render resource using entri point(s).
     *
     * @param string ...$entryPoints
     * @return string
     * @throws Exception
     */
    public function __invoke(string ...$entryPoints): string
    {
        if (empty($entryPoints)) {
            return '';
        }

        if ($this->isRunningHRM()) {
            $tags   = [];
            $tags[] = $this->getHmrScript();
            $hmrUrl = $this->getHmrUrl();

            foreach ($entryPoints as $entryPoint) {
                $url    = $hmrUrl . $entryPoint;
                $tags[] = $this->createTag($url, $entryPoint);
            }

            return implode("\n", $tags);
        }

        $imports = $this->getManifestImports($entryPoints);
        $preload = [];
        foreach ($imports['imports'] as $entryPoint) {
            $url       = $this->getManifest($entryPoint);
            $preload[] = $this->createPreloadTag($url);
        }

        foreach ($imports['css'] as $entryPoint) {
            $preload[] = $this->createStyleTag($this->buildPath . $entryPoint);
        }

        $assets    = $this->gets($entryPoints);
        $cssAssets = array_filter(
            $assets,
            fn ($file, $url) => $this->isCssFile($file),
            ARRAY_FILTER_USE_BOTH
        );

        $jsAssets = array_diff_key($assets, $cssAssets);
        $tags     = array_merge(
            $preload,
            array_map(fn ($url) => $this->createStyleTag($url), $cssAssets),
            array_map(fn ($url) => $this->createScriptTag($url), $jsAssets)
        );

        return implode("\n", $tags);
    }

    /**
     * @param string $manifestName
     * @return $this
     */
    public function manifestName(string $manifestName): self
    {
        $this->manifestName = $manifestName;

        return $this;
    }

    /**
     * Flush the cache.
     *
     * @return void
     */
    public static function flush(): void
    {
        static::$cache = [];
        static::$hot   = null;
    }

    /**
     * Get manifest filename.
     *
     * @return string
     * @throws Exception if manifest file is not found.
     */
    public function manifest(): string
    {
        if (file_exists($fileName = "{$this->publicPath}/{$this->buildPath}/{$this->manifestName}")) {
            return $fileName;
        }

        throw new Exception("Manifest file not found {$fileName}");
    }

    /**
     * @return array<string, array<string, string|string[]>>
     * @throws Exception
     */
    public function loader(): array
    {
        $fileName    = $this->manifest();
        $currentTime = $this->manifestTime();

        if (array_key_exists($fileName, static::$cache)
            && $this->cacheTime === $currentTime) {
            return static::$cache[$fileName];
        }

        $this->cacheTime = $currentTime;
        $load            = file_get_contents($fileName);

        if ($load === false) {
            throw new Exception("Failed to read manifest file: {$fileName}");
        }

        $json = json_decode($load, true);

        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Manifest JSON decode error: ' . json_last_error_msg());
        }

        return static::$cache[$fileName] = $json;
    }

    /**
     * @param string $resourceName
     * @return string
     * @throws Exception
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
     * @param string[] $resourceNames
     * @return array<string, string>
     * @throws Exception
     *
     * @deprecated Since v0.40
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
     * @param string[] $resources
     * @return array{imports: string[], css: string[]}
     * @throws Exception
     */
    public function getManifestImports(array $resources): array
    {
        $assets      = $this->loader();
        $resourceSet = array_fill_keys($resources, true);

        $preload = ['imports' => [], 'css' => []];

        foreach ($assets as $name => $asset) {
            if (isset($resourceSet[$name])) {
                $this->collectImports($assets, $asset, $preload);
            }
        }

        $preload['imports'] = array_values(array_unique($preload['imports']));
        $preload['css']     = array_values(array_unique($preload['css']));

        return $preload;
    }

    /**
     * @param array<string, array<string, string|string[]>> $assets
     * @param array<string, string|string[]>                $asset
     * @param array{imports: string[], css: string[]}       $preload
     */
    private function collectImports(array $assets, array $asset, array &$preload): void
    {
        if (false === empty($asset['css'])) {
            $preload['css'] = array_merge($preload['css'], $asset['css']);
        }

        if (false === empty($asset['imports'])) {
            foreach ($asset['imports'] as $import) {
                $preload['imports'][] = $import;

                if (isset($assets[$import])) {
                    $this->collectImports($assets, $assets[$import], $preload);
                }
            }
        }
    }

    /**
     * Get hot url (if hot not found will return with manifest).
     *
     * @param string $resourceName
     * @return string
     * @throws Exception
     */
    /**
     * @param string $resourceName
     * @return string
     * @throws Exception
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
     * Get hot url (if hot not found will return with manifest).
     *
     * @param string[] $resourceNames
     * @return array<string, string>
     * @throws Exception
     */
    public function gets(array $resourceNames): array
    {
        if (false === $this->isRunningHRM()) {
            $asset     = $this->loader();
            $resources = [];

            foreach ($resourceNames as $resource) {
                if (array_key_exists($resource, $asset)) {
                    $resources[$resource] = $this->buildPath . $asset[$resource]['file'];
                }
            }

            return $resources;
        }

        $hot  = $this->getHmrUrl();

        return array_combine(
            $resourceNames,
            array_map(fn ($asset) => $hot . $asset, $resourceNames)
        );
    }

    /**
     * Determine if the HMR server is running.
     *
     * @return bool
     */
    public function isRunningHRM(): bool
    {
        return is_file("{$this->publicPath}/hot");
    }

    /**
     * Get hot url.
     *
     * @return string
     * @throws Exception
     */
    public function getHmrUrl(): string
    {
        if (!is_null(static::$hot)) {
            return static::$hot;
        }

        $hotFile = "{$this->publicPath}/hot";
        $hot     = file_get_contents($hotFile);

        if ($hot === false) {
            throw new Exception("Failed to read hot file: {$hotFile}");
        }

        $hot  = rtrim($hot);
        $dash = str_ends_with($hot, '/') ? '' : '/';

        return static::$hot = $hot . $dash;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getHmrScript(): string
    {
        return '<script type="module" src="' . $this->getHmrUrl() . '@vite/client"></script>';
    }

    /**
     * @return int
     */
    public function cacheTime(): int
    {
        return $this->cacheTime;
    }

    /**
     * @return int
     * @throws Exception
     */
    public function manifestTime(): int
    {
        return filemtime($this->manifest());
    }

    /**
     * @param string[] $entryPoints
     * @return string
     * @throws Exception
     */
    public function getPreloadTags(array $entryPoints): string
    {
        if ($this->isRunningHRM()) {
            return '';
        }

        $tags    = [];
        $imports = $this->getManifestImports($entryPoints);

        foreach ($imports['imports'] as $entryPoint) {
            $url    = $this->getManifest($entryPoint);
            $tags[] = $this->createPreloadTag($url);
        }

        foreach ($imports['css'] as $entryPoint) {
            $tags[] = $this->createStyleTag($this->buildPath . $entryPoint);
        }

        return implode("\n", $tags);
    }

    /**
     * @param string[]                                $entryPoints
     * @param array<string|int, string|bool|int|null> $attributes
     * @return string
     * @throws Exception
     */
    public function getTags(array $entryPoints, ?array $attributes = null): string
    {
        return $this->getCustomTags(
            array_combine($entryPoints, array_fill(0, count($entryPoints), $attributes ?? []))
        );
    }

    /**
     * @param array<string, array<string|int, string|bool|int|null>> $entryPoints
     * @param array<string|int, string|bool|int|null>                $defaultAttributes
     * @return string
     * @throws Exception
     */
    public function getCustomTags(array $entryPoints, array $defaultAttributes = []): string
    {
        $tags = [];

        if ($this->isRunningHRM()) {
            $tags[] = $this->getHmrScript();
        }

        $assets    = $this->gets(array_keys($entryPoints));
        $cssAssets = array_filter(
            $assets,
            fn ($file, $url) => $this->isCssFile($file),
            ARRAY_FILTER_USE_BOTH
        );

        $jsAssets = array_diff_key($assets, $cssAssets);
        $tags     = array_merge(
            array_map(
                fn ($url, $file) => $this->createStyleTag($url, $entryPoints[$file] ?? $defaultAttributes),
                array_values($cssAssets),
                array_keys($cssAssets)
            ),
            array_map(
                fn ($url, $file) => $this->createScriptTag($url, $entryPoints[$file] ?? $defaultAttributes),
                array_values($jsAssets),
                array_keys($jsAssets)
            )
        );

        return implode("\n", $tags);
    }

    /**
     * @param string                                       $url
     * @param string                                       $entryPoint
     * @param array<string|int, string|bool|int|null>|null $attributes
     * @return string
     */
    private function createTag(string $url, string $entryPoint, ?array $attributes = null): string
    {
        if ($this->isCssFile($entryPoint)) {
            return $this->createStyleTag($url);
        }

        return $this->createScriptTag($url, $attributes);
    }

    /**
     * @param string                                       $url
     * @param array<string|int, string|bool|int|null>|null $attributes
     * @return string
     */
    private function createScriptTag(string $url, ?array $attributes = null): string
    {
        $attributes ??= [];

        if (false === isset($attributes['type'])) {
            $attributes = array_merge(['type' => 'module'], $attributes);
        }

        $attributes['src'] = $this->escapeUrl($url);
        $attributes        = $this->buildAttributeString($attributes);

        return "<script {$attributes}></script>";
    }

    /**
     * @param string                                       $url
     * @param array<string|int, string|bool|int|null>|null $attributes
     * @return string
     */
    private function createStyleTag(string $url, ?array $attributes = null): string
    {
        if ($this->isRunningHRM()) {
            return $this->createScriptTag($url, $attributes);
        }

        $attributes ??= [];
        $attributes['rel']  = 'stylesheet';
        $attributes['href'] = $this->escapeUrl($url);
        $attributes         = $this->buildAttributeString($attributes);

        return "<link {$attributes}>";
    }

    /**
     * @param string $url
     * @return string
     */
    private function createPreloadTag(string $url): string
    {
        $attributes = $this->buildAttributeString([
            'rel'  => 'modulepreload',
            'href' => $this->escapeUrl($url),
        ]);

        return "<link {$attributes}>";
    }

    // helper functions

    /**
     * @param string $filename
     * @return bool
     */
    private function isCssFile(string $filename): bool
    {
        return preg_match('/\.(css|less|sass|scss|styl|stylus|pcss|postcss)$/', $filename) === 1;
    }

    /**
     * @param string $url
     * @return string
     */
    private function escapeUrl(string $url): string
    {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Build attribute string from array.
     *
     * @param array<string|int, string|bool|int|null> $attributes
     * @return string
     */
    private function buildAttributeString(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }

        $parts = [];
        foreach ($attributes as $key => $value) {
            if (is_int($key)) {
                $parts[] = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
                continue;
            }

            $key = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');

            $part = match (true) {
                is_bool($value) => $value ? $key : null,
                $value === null => null,
                default         => $key . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"',
            };

            if ($part !== null) {
                $parts[] = $part;
            }
        }

        return implode(' ', $parts);
    }
}
