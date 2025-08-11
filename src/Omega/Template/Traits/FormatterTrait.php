<?php

declare(strict_types=1);

namespace Omega\Template\Traits;

/**
 * Trait for format the printer.
 */
trait FormatterTrait
{
    protected int $tabSize = 1;

    protected string $tabIndent = "\t";

    private string $customizeTemplate;

    public function tabSize(int $tabSize): self
    {
        $this->tabSize = $tabSize;

        return $this;
    }

    public function tabIndent(string $tabIndent): self
    {
        $this->tabIndent = $tabIndent;

        return $this;
    }

    public function customizeTemplate(string $template): self
    {
        $this->customizeTemplate = $template;

        return $this;
    }
}
