<?php

declare(strict_types=1);

namespace Omega\View;

use Exception;
use Omega\View\Templator\BooleanTemplator;
use Omega\View\Templator\BreakTemplator;
use Omega\View\Templator\CommentTemplator;
use Omega\View\Templator\ComponentTemplator;
use Omega\View\Templator\ContinueTemplator;
use Omega\View\Templator\DirectiveTemplator;
use Omega\View\Templator\EachTemplator;
use Omega\View\Templator\IfTemplator;
use Omega\View\Templator\IncludeTemplator;
use Omega\View\Templator\JsonTemplator;
use Omega\View\Templator\NameTemplator;
use Omega\View\Templator\PHPTemplator;
use Omega\View\Templator\SectionTemplator;
use Omega\View\Templator\SetTemplator;
use Omega\View\Templator\UseTemplator;
use Throwable;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function is_string;
use function ltrim;
use function md5;
use function ob_end_clean;
use function ob_get_clean;
use function ob_get_level;
use function ob_start;

class Templator
{
    protected TemplatorFinder $finder;
    private string $cacheDir;

    public string $suffix = '';

    public int $maxDepth = 5;

    private string $componentNamespace = '';

    /** @var array<string, array<string, int>> */
    private array $dependency = [];

    /**
     * Create new instance.
     *
     * @param string|TemplatorFinder $finder   If String will generate TemplatorFinder with default extension.
     * @param string                 $cacheDir
     * @return void
     */
    public function __construct(TemplatorFinder|string $finder, string $cacheDir)
    {
        // Backwards compatibility with templator finder.
        $this->finder   = is_string($finder) ? new TemplatorFinder([$finder]) : $finder;
        $this->cacheDir = $cacheDir;
    }

    /**
     * Set Finder.
     *
     * @param TemplatorFinder $finder
     * @return self
     */
    public function setFinder(TemplatorFinder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    /**
     * Set Component Namespace.
     *
     * @param string $namespace
     * @return self
     */
    public function setComponentNamespace(string $namespace): self
    {
        $this->componentNamespace = $namespace;

        return $this;
    }

    /**
     * Add dependency.
     *
     * @param string $parent
     * @param string $child
     * @param int    $dependDeep
     * @return self
     */
    public function addDependency(string $parent, string $child, int $dependDeep = 1): self
    {
        $this->dependency[$parent][$child] = $dependDeep;

        return $this;
    }

    /**
     * Prepend Dependency.
     *
     * @param string             $parent
     * @param array<string, int> $children
     * @return self
     */
    public function prependDependency(string $parent, array $children): self
    {
        foreach ($children as $child => $depth) {
            if ($hasDepth = isset($this->dependency[$parent][$child]) && $depth > $this->dependency[$parent][$child]) {
                $this->addDependency($parent, $child, $depth);
            }

            if (false === $hasDepth) {
                $this->addDependency($parent, $child, $depth);
            }
        }

        return $this;
    }

    /**
     * Get dependency.
     *
     * @param string $parent
     * @return array<string, int>
     */
    public function getDependency(string $parent): array
    {
        return $this->dependency[$parent] ?? [];
    }

    /**
     * Render
     *
     * @param string $templateName
     * @param array<string, mixed> $data
     * @param bool $cache
     * @return string
     * @throws Throwable
     */
    public function render(string $templateName, array $data, bool $cache = true): string
    {
        $templateName .= $this->suffix;
        $templatePath  = $this->finder->find($templateName);
        $cachePath     = $this->cacheDir . '/' . md5($templateName) . '.php';

        if ($cache && file_exists($cachePath) && filemtime($cachePath) >= filemtime($templatePath)) {
            return $this->getView($cachePath, $data);
        }

        $template = file_get_contents($templatePath);
        $template = $this->templates($template, $templatePath);

        file_put_contents($cachePath, $template);

        return $this->getView($cachePath, $data);
    }

    /**
     * Compile templator file to php file.
     *
     * @param string $templateName
     * @return string
     * @throws Exception
     */
    public function compile(string $templateName): string
    {
        $templateName .= $this->suffix;
        $templateDir   = $this->finder->find($templateName);

        $cachePath = $this->cacheDir . '/' . md5($templateName) . '.php';

        $template = file_get_contents($templateDir);
        $template = $this->templates($template, $templateDir);

        file_put_contents($cachePath, $template);

        return $template;
    }

    /**
     * Check view file exist.
     *
     * @param string $templateName
     * @return bool
     */
    public function viewExist(string $templateName): bool
    {
        $templateName .= $this->suffix;

        return $this->finder->exists($templateName);
    }

    /**
     * Get view.
     *
     * @param string               $templatePath
     * @param array<string, mixed> $data
     * @return string
     */
    private function getView(string $templatePath, array $data): string
    {
        $level = ob_get_level();

        ob_start();

        try {
            (static function ($__, $__file_name__) {
                extract($__);
                include $__file_name__;
            })($data, $templatePath);
        } catch (Throwable $th) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $th;
        }

        $out = ob_get_clean();

        return $out === false ? '' : ltrim($out);
    }

    /**
     * Transform templator to php template.
     *
     * @param string $template
     * @param string $viewLocation
     * @return string
     * @throws Exception
     */
    public function templates(string $template, string $viewLocation = ''): string
    {
        return array_reduce([
            SetTemplator::class,
            SectionTemplator::class,
            IncludeTemplator::class,
            PHPTemplator::class,
            DirectiveTemplator::class,
            ComponentTemplator::class,
            NameTemplator::class,
            IfTemplator::class,
            EachTemplator::class,
            CommentTemplator::class,
            ContinueTemplator::class,
            BreakTemplator::class,
            UseTemplator::class,
            JsonTemplator::class,
            BooleanTemplator::class,
        ], function (string $template, string $templator) use ($viewLocation): string {
            $templator = new $templator($this->finder, $this->cacheDir);
            if ($templator instanceof IncludeTemplator) {
                $templator->maksDept($this->maxDepth);
            }

            if ($templator instanceof ComponentTemplator) {
                $templator->setNamespace($this->componentNamespace);
            }

            $parse = $templator->parse($template);

            // Get dependency view file (parent) after parse template.
            if ($templator instanceof DependencyTemplatorInterface) {
                $this->prependDependency($viewLocation, $templator->dependOn());
            }

            return $parse;
        }, $template);
    }
}
