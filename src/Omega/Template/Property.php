<?php

declare(strict_types=1);

namespace Omega\Template;

use Omega\Template\Traits\CommentTrait;
use Omega\Template\Traits\FormatterTrait;
use function array_filter;
use function count;
use function implode;
use function is_array;
use function str_repeat;
use function str_replace;
use const ARRAY_FILTER_USE_KEY;

class Property
{
    use FormatterTrait;
    use CommentTrait;

    private bool $isStatic = false;

    private int $visibility = self::PRIVATE_;

    public const int PUBLIC_ = 0;

    public const int PRIVATE_ = 1;

    public const int PROTECTED_ = 2;

    private string $dataType;
    private string $name;

    /** @var string[] */
    private array $expecting;

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

    private function planTemplate(): string
    {
        return $this->customize_template ?? '{{comment}}{{visibility}}{{static}}{{data type}}{{name}}{{expecting}};';
    }

    public function generate(): string
    {
        $template = $this->planTemplate();
        $tabDept  = fn (int $dept) => str_repeat($this->tabIndent, $dept * $this->tabSize);

        $comment = $this->generateComment(1);
        $comment = count($this->comments) > 0
      ? $comment . "\n" . $tabDept(1)
      : $comment;

        // generate visibility
        $visibility = '';
        switch ($this->visibility) {
            case self::PUBLIC_:
                $visibility = 'public ';
                break;

            case self::PROTECTED_:
                $visibility = 'protected ';
                break;

            case self::PRIVATE_:
                $visibility = 'private ';
                break;
        }

        // generate static
        $static = $this->isStatic ? 'static ' : '';

        // data type
        $data_type = $this->dataType ?? '';

        // generate name
        $name = '$' . $this->name;

        // generate value or expecting
        $expecting = '';
        if ($this->expecting !== null) {
            $singleLine  = $this->expecting[0] ?? '';
            $multiLine   = implode(
                "\n" . $tabDept(1),
                array_filter($this->expecting, fn ($key) => $key > 0, ARRAY_FILTER_USE_KEY)
            );
            $expecting = count($this->expecting) > 1
        ? ' ' . $singleLine . "\n" . $tabDept(1) . $multiLine
        : ' ' . $singleLine;
        }

        // final
        return str_replace(
            ['{{comment}}', '{{visibility}}', '{{static}}', '{{data type}}', '{{name}}', '{{expecting}}'],
            [$comment, $visibility, $static, $data_type, $name, $expecting],
            $template
        );
    }

    // setter
    public function setStatic(bool $isStatic = true): self
    {
        $this->isStatic = $isStatic;

        return $this;
    }

    public function visibility(int $visibility = self::PUBLIC_): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function dataType(string $dataType): self
    {
        $this->dataType = $dataType . ' ';

        return $this;
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string|string[] $expecting Add expecting as string or array for multi line
     */
    public function expecting(array|string $expecting): self
    {
        $this->expecting = is_array($expecting)
            ? $expecting
            : [$expecting];

        return $this;
    }
}
