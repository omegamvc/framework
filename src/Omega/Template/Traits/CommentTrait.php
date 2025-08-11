<?php

declare(strict_types=1);

namespace Omega\Template\Traits;

use function array_unshift;
use function count;
use function implode;
use function str_repeat;
use function str_replace;

/**
 * Trait for adding comment.
 */
trait CommentTrait
{
    /** @var string[] */
    private array $comments = [];

    public function addComment(?string $comment): self
    {
        $this->comments[] = $comment ?? '';

        return $this;
    }

    public function addLineComment(): self
    {
        return $this->addComment(null);
    }

    public function addParamComment(string $datatype, string $name, string $description): self
    {
        $name        = $name == '' ? $name : ' ' . $name;
        $description = $description == '' ? $description : ' ' . $description;

        $this->comments[] = "@param $datatype$name$description";

        return $this;
    }

    public function addVariableComment(string $datatype, string $name = ''): self
    {
        $name = $name == '' ? $name : ' ' . $name;

        $this->comments[] = "@var $datatype$name";

        return $this;
    }

    public function addReturnComment(string $datatype, string $name = '', string $description = ''): self
    {
        $name        = $name == '' ? $name : ' ' . $name;
        $description = $description == '' ? $description : ' ' . $description;

        $this->comments[] = "@return $datatype$name$description";

        return $this;
    }

    public function commentTemplate(): string
    {
        return '/** {{body}} */';
    }

    public function generateComment(int $tabSize = 0, string $tabIndent = "\t"): string
    {
        $template     = $this->commentTemplate();
        $countComment = count($this->comments);
        $endLine      = '';
        $tabDept      = str_repeat($tabIndent, $tabSize);

        if ($countComment > 0) {
            if ($countComment > 1) {
                array_unshift($this->comments, '');
                $endLine = "\n$tabDept";
            }

            $comment = implode("\n$tabDept * ", $this->comments) . $endLine;

            return str_replace('{{body}}', $comment, $template);
        }

        // return empty if comment not available
        return '';
    }
}
