<?php

declare(strict_types=1);

namespace Omega\View;

abstract class AbstractTemplatorParse
{
    /**
     * Uses poller.
     *
     * @var string[]
     */
    protected array $uses = [];

    /**
     * Constructor.
     *
     * @param TemplatorFinder $finder
     * @param string $cacheDir
     */
    final public function __construct(protected TemplatorFinder $finder, protected string $cacheDir)
    {
    }

    /**
     * Parse.
     *
     * @param string $template
     * @return string
     */
    abstract public function parse(string $template): string;
}
