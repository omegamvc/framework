<?php /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

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
use function is_null;
use function json_decode;
use function rtrim;

class Vite
{
    private string $publicPath;

    private string $buildPath;

    private string $manifestName;

    private int $cacheTime = 0;

    /** @var array<string, array<string, array<string, string>>> */
    public static array $cache = [];

    public static ?string $hot = null;

    public function __construct(string $publicPath, string $buildPath)
    {
        $this->publicPath          = $publicPath;
        $this->buildPath           = $buildPath;
        $this->manifestName        = 'manifest.json';
    }

    /**
     * Get resource using entri point(s).
     *
     * @param string ...$entryPoints
     * @return array<string, string>|string
     *                                      If entry point is string will return string,
     *                                      otherwise if entry point is array return as array
     * @throws Exception
     */
    public function __invoke(string ...$entryPoints): array|string
    {
        $resource = $this->gets($entryPoints);
        $first    = array_key_first($resource);

        return 1 === count($resource) ? $resource[$first] : $resource;
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
     * @return array<string, array<string, string>>
     * @throws Exception
     */
    public function loader(): array
    {
        $fileName = $this->manifest();

        if (array_key_exists($fileName, static::$cache)) {
            return static::$cache[$fileName];
        }

        $this->cacheTime = $this->manifestTime();
        $load            = file_get_contents($fileName);
        $json            = json_decode($load, true);

        if (false === $json) {
            throw new Exception('Manifest doest support');
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
     * Get hot url (if hot not found will return with manifest).
     *
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
     * @return string
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
}
