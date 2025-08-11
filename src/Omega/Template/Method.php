<?php

declare(strict_types=1);

namespace Omega\Template;

use Omega\Template\Traits\CommentTrait;
use Omega\Template\Traits\FormatterTrait;
use function array_filter;
use function array_map;
use function count;
use function implode;
use function is_array;
use function str_repeat;

class Method
{
    use FormatterTrait;
    use CommentTrait;

    public const int PUBLIC_ = 0;

    public const int PRIVATE_ = 1;

    public const int PROTECTED_ = 2;

    private int $visibility = -1;

    private bool $isFinal = false;

    private bool $isStatic = false;

    private string $name;

    /** @var string[] */
    private array $params = [];

    private ?string $returnType = null;

    /** @var string[] */
    private array $body = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->generate();
    }

    public static function new(string $name): self
    {
        return new self($name);
    }

    public function planTemplate(): string
    {
        return $this->customizeTemplate
            ?? "{{comment}}{{before}}function {{name}}({{params}}){{return type}}{{new line}}{\n{{body}}{{new line}}}";
    }

    public function generate(): string
    {
        $template = $this->planTemplate();
        $tabDept  = fn (int $dept) => str_repeat($this->tabIndent, $dept * $this->tabSize);
        // new line
        $newLine = "\n" . $tabDept(1);

        // comment
        $comment = $this->generateComment(1, $this->tabIndent);
        $comment = count($this->comments) > 0
            ? $comment . $newLine
            : $comment;

        $pre = [];
        // final
        $pre[] = $this->isFinal ? 'final' : '';

        // static
        $pre[] = $this->isStatic ? 'static' : '';

        // visibility
        $pre[] = match ($this->visibility) {
            self::PUBLIC_    => 'public',
            self::PRIVATE_   => 'private',
            self::PROTECTED_ => 'protected',
            default          => '',
        };

        // {{final}}{{visibility}}{{static}}
        $pre    = array_filter($pre);
        $before = implode(' ', $pre);
        $before .= count($pre) == 0 ? '' : ' ';

        // name
        $name = $this->name;

        // params
        $params = implode(', ', $this->params);

        // return type
        $return = isset($this->returnType) ? ': ' : '';
        $return .= $this->returnType;

        // body
        $bodies = array_map(fn ($x) => $tabDept(2) . $x, $this->body);
        $body   = implode("\n", $bodies);

        return str_replace(
            ['{{comment}}', '{{before}}', '{{name}}', '{{params}}', '{{new line}}', '{{body}}', '{{return type}}'],
            [$comment, $before, $name, $params, $newLine, $body, $return],
            $template
        );
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function visibility(int $visibility = self::PUBLIC_): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function isFinal(bool $is_final = true): self
    {
        $this->isFinal = $is_final;

        return $this;
    }

    public function isStatic(bool $is_static = true): self
    {
        $this->isStatic = $is_static;

        return $this;
    }

    /**
     * @param string[]|null $params
     */
    public function params(?array $params): self
    {
        $this->params = $params ?? [];

        return $this;
    }

    public function addParams(string $param): self
    {
        $this->params[] = $param;

        return $this;
    }

    public function setReturnType(?string $returnType): self
    {
        $this->returnType = $returnType ?? '';

        return $this;
    }

    /**
     * @param string|string[]|null $body Raw string body (delimiter multi line with array)
     */
    public function body(array|string|null $body): self
    {
        $body ??= [];

        $this->body = is_array($body)
            ? $body
            : [$body];

        return $this;
    }
}
