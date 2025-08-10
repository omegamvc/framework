<?php

declare(strict_types=1);

namespace Omega\View\Templator;

use Exception;
use Omega\View\AbstractTemplatorParse;
use Omega\View\DependencyTemplatorInterface;
use Omega\View\InteractWithCacheTrait;

use function preg_replace_callback;
use function trim;

class IncludeTemplator extends AbstractTemplatorParse implements DependencyTemplatorInterface
{
    use InteractWithCacheTrait;

    private int $makeDept = 5;

    private int $dept = 0;

    /**
     * Depend on.
     *
     * @var array<string, int>
     */
    private array $dependOn = [];

    /**
     * File get content cached.
     *
     * @var array<string, string>
     */
    private static array $cache = [];

    public function maksDept(int $makeDept): self
    {
        $this->makeDept = $makeDept;

        return $this;
    }

    /**
     * @return array<string, int>
     */
    public function dependOn(): array
    {
        return $this->dependOn;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function parse(string $template): string
    {
        self::$cache = [];

        return preg_replace_callback(
            '/{%\s*include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/',
            function ($matches) {
                if (false === $this->finder->exists($matches[1])) {
                    throw new Exception('Template file not found: ' . $matches[1]);
                }

                $templatePath     = $this->finder->find($matches[1]);
                $includedTemplate = $this->getContents($templatePath);

                if ($this->makeDept === 0) {
                    return $includedTemplate;
                }

                $this->makeDept--;
                $this->dependOn[$templatePath] = ++$this->dept;

                return trim($this->parse($includedTemplate));
            },
            $template
        );
    }
}
