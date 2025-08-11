<?php

declare(strict_types=1);

namespace Omega\View\Templator;

use Exception;
use Omega\View\AbstractTemplatorParse;
use Omega\View\DependencyTemplatorInterface;
use Omega\View\Exceptions\ViewFileNotFoundException;
use Omega\View\Exceptions\YeldSectionNotFoundException;
use Omega\View\InteractWithCacheTrait;

use function array_key_exists;
use function class_exists;
use function explode;
use function preg_replace_callback;
use function str_contains;
use function trim;

class ComponentTemplator extends AbstractTemplatorParse implements DependencyTemplatorInterface
{
    use InteractWithCacheTrait;

    /**
     * File get content cached.
     *
     * @var array<string, string>
     */
    private static array $cache = [];

    /**
     * Namespace.
     *
     * @var string
     */
    private string $namespace = '';

    /**
     * Depend on.
     *
     * @var array<string, int>
     */
    private array $dependOn = [];

    /**
     * DependOn.
     *
     * @return array<string, int>
     */
    public function dependOn(): array
    {
        return $this->dependOn;
    }

    /**
     * Parse.
     *
     * @param string $template
     * @return string
     * @throws Exception
     */
    public function parse(string $template): string
    {
        self::$cache = [];

        return $this->parseComponent($template);
    }

    /**
     * Set namespace.
     *
     * @param string $namespace
     * @return $this
     */
    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Parse component.
     *
     * @param string $template
     * @return string
     * @throws YeldSectionNotFoundException
     */
    private function parseComponent(string $template): string
    {
        return preg_replace_callback(
            '/{%\s*component\(\s*(.*?)\)\s*%}(.*?){%\s*endcomponent\s*%}/s',
            function ($matches) use ($template) {
                if (!array_key_exists(1, $matches)) {
                    return $template;
                }
                if (!array_key_exists(2, $matches)) {
                    return $template;
                }

                $rawParams                = trim($matches[1]);
                [$componentName, $params] = $this->extractComponentAndParams($rawParams);
                $innerContent             = $matches[2];

                if (class_exists($class = $this->namespace . $componentName)) {
                    $component = new $class(...$params);

                    return $component->render($innerContent);
                }

                if (false === $this->finder->exists($componentName)) {
                    throw new ViewFileNotFoundException($componentName);
                }

                $templatePath = $this->finder->find($componentName);
                $layout       = $this->getContents($templatePath);
                $content      = $this->parseComponent($layout);
                // add parent dependency
                $this->dependOn[$templatePath] = 1;

                return preg_replace_callback(
                    "/{%\s*yield\(\'([^\']+)\'\)\s*%}/",
                    function ($yieldMatches) use ($componentName, $innerContent, $params) {
                        if ($componentName === $yieldMatches[1]) {
                            return $innerContent;
                        }

                        if (array_key_exists($yieldMatches[1], $params)) {
                            return $params[$yieldMatches[1]];
                        }

                        throw new YeldSectionNotFoundException('yield section not found: ' . $yieldMatches[1]);
                    },
                    $content
                );
            },
            $template
        );
    }

    /**
     * Extract component name and parameters from raw params.
     *
     * @param string $rawParams
     * @return array{0: string, 1: array<string, string>}
     */
    private function extractComponentAndParams(string $rawParams): array
    {
        $parts         = explode(',', $rawParams, 2);
        $componentName = trim($parts[0], "'\"");

        $paramsString = $parts[1] ?? '';
        $params       = [];
        foreach (explode(',', $paramsString) as $param) {
            $param = trim($param);
            if (str_contains($param, ':')) {
                [$key, $value] = explode(':', $param, 2);
                $key           = trim($key);
                $value         = trim($value, "'\" ");
                $params[$key]  = $value;
            } elseif (!empty($param)) {
                $params[] = trim($param, "'\" ");
            }
        }

        return [$componentName, $params];
    }
}
