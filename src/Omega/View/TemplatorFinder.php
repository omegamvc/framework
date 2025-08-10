<?php

declare(strict_types=1);

namespace Omega\View;

use Omega\View\Exceptions\ViewFileNotFoundException;

use function array_unshift;
use function file_exists;
use function in_array;
use function realpath;

class TemplatorFinder
{
    /**
     * View file location has register.
     *
     * @var array<string, string>
     */
    protected array $views = [];

    /**
     * Paths.
     *
     * @var string[]
     */
    protected array $paths = [];

    /**
     * Extensions.
     *
     * @var string[]
     */
    protected array $extensions;

    /**
     * Create new View Finder instance.
     *
     * @param string[] $paths
     * @param string[] $extensions
     */
    public function __construct(array $paths, ?array $extensions = null)
    {
        $this->setPaths($paths);
        $this->extensions = $extensions ?? ['.template.php', '.php'];
    }

    /**
     * Find file location by view_name given.
     *
     * @param string $viewName
     * @return string
     * @throws ViewFileNotFoundException
     */
    public function find(string $viewName): string
    {
        if (isset($this->views[$viewName])) {
            return $this->views[$viewName];
        }

        return $this->views[$viewName] = $this->findInPath($viewName, $this->paths);
    }

    /**
     * Check view name exist.
     *
     * @param string $viewName
     * @return bool
     */
    public function exists(string $viewName): bool
    {
        if (isset($this->views[$viewName])) {
            return true;
        }

        foreach ($this->paths as $path) {
            foreach ($this->extensions as $extension) {
                if (file_exists($file = $path . DIRECTORY_SEPARATOR . $viewName . $extension)) {
                    $this->views[$viewName] = $file;

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Find view name possible paths given.
     *
     * @param string   $viewName
     * @param string[] $paths
     * @return string
     * @throws ViewFileNotFoundException
     */
    protected function findInPath(string $viewName, array $paths): string
    {
        foreach ($paths as $path) {
            foreach ($this->extensions as $extension) {
                if (file_exists($find = $path . DIRECTORY_SEPARATOR . $viewName . $extension)) {
                    return $find;
                }
            }
        }

        throw new ViewFileNotFoundException($viewName);
    }

    /**
     * Add path to possible path location.
     *
     * @param string $path
     * @return self
     */
    public function addPath(string $path): self
    {
        if (false === in_array($path, $this->paths)) {
            $this->paths[] = $this->resolvePath($path);
        }

        return $this;
    }

    /**
     * Add extension in first array.
     *
     * @param string $extension
     * @return self
     */
    public function addExtension(string $extension): self
    {
        array_unshift($this->extensions, $extension);

        return $this;
    }

    /**
     * Flush view register file location.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->views = [];
    }

    /**
     * Set paths registered.
     *
     * @param string[] $paths
     * @return self
     */
    public function setPaths(array $paths): self
    {
        $this->paths = [];
        foreach ($paths as $path) {
            $this->paths[] = $this->resolvePath($path);
        }

        return $this;
    }

    /**
     * Get paths registered.
     *
     * @return string[]
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Get Extension registered.
     *
     * @return string[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Resolve the path.
     *
     * @param string $path
     * @return string
     */
    protected function resolvePath(string $path): string
    {
        return realpath($path) ?: $path;
    }
}
