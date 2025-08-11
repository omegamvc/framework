<?php

declare(strict_types=1);

namespace Omega\Template;

use Omega\Template\Traits\CommentTrait;
use Omega\Template\Traits\FormatterTrait;
use function array_filter;
use function call_user_func_array;
use function count;
use function file_put_contents;
use function implode;
use function is_array;
use function is_callable;
use function str_repeat;
use function str_replace;

class Generate
{
    use FormatterTrait;
    use CommentTrait;

    public const int SET_CLASS = 0;

    public const int SET_ABSTRACT = 1;

    public const int SET_TRAIT = 2;

    private bool $isFinal = false;

    private int $rule;

    private bool $endWithNewline = false;

    private ?string $name;

    private ?string $namespace = null;

    /** @var string[] */
    private array $uses = [];

    private ?string $extend = null;

    /** @var string[] */
    private array $implements = [];

    /** @var string[] */
    private array $traits = [];

    /** @var string[] */
    private array $constants = [];

    /** @var string[] */
    private array $properties = [];

    /** @var string[] */
    private array $methods = [];

    /** @var string[] */
    private array $body = [];

    /** @var string[][] */
    private array $preReplace = [[], []];

    /** @var string[][] */
    private array $replace = [[], []];

    public function __construct(string $name = 'NewClass')
    {
        $this->name = $name;
        $this->rule = Generate::SET_CLASS;
    }

    public function __invoke(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public static function static(string $name): self
    {
        return new self($name);
    }

    public function __toString(): string
    {
        return $this->generate();
    }

    private function planTemplate(): string
    {
        return $this->customizeTemplate
            ?? "<?php\n{{before}}{{comment}}\n{{rule}}class\40{{head}}\n{\n{{body}}\n}{{end}}";
    }

    public function generate(): string
    {
        // pre replace
        $class = str_replace(
            $this->preReplace[0],
            $this->preReplace[1],
            $this->planTemplate()
        );

        $tabDept = fn (int $dept) => str_repeat($this->tabIndent, $dept * $this->tabSize);

        // scope: before
        $before = [];
        if ($this->namespace !== null || count($this->uses) > 0) {
            $before[] = '';
        }

        // generate namespace
        if ($this->namespace !== null) {
            $before[] = 'namespace ' . $this->namespace . ";\n";
        }

        // generate uses
        if (count($this->uses) > 0) {
            $before[] = 'use ' . implode(";\nuse ", $this->uses) . ';';
            $before[] = '';
        }

        $before = implode("\n", $before);

        // scope comment, generate comment
        $comment = $this->generateComment(0, $this->tabIndent);

        // generate class rule
        $rule = $this->rule == 0
            ? ''
            : $this->ruleText() . ' ';

        // generate final
        $rule = !$this->isFinal
            ? $rule
            : 'final ' . $rule;

        // scope: head
        // generate class name
        $head = [];

        // generate class name
        $head[] = $this->name;

        // generate extend
        if ($this->extend !== null) {
            $head[] = 'extends ' . $this->extend;
        }

        // generate implements
        if (count($this->implements) > 0) {
            $head[] = 'implements ' . implode(', ', $this->implements);
        }

        $head = implode(' ', $head);

        // scope: body
        $body = [];
        // generate traits
        if (count($this->traits) > 0) {
            $body[] = $tabDept(1) . 'use ' . implode(', ', $this->traits) . ";\n";
        }

        // generate constants
        $constants = [];
        if (count($this->constants) > 0) {
            foreach ($this->constants as $const) {
                /* @phpstan-ignore-next-line */
                if ($const instanceof Constant) {
                    $const
                        ->tabSize($this->tabSize)
                        ->tabIndent($this->tabIndent);
                    $constants[] = $tabDept(1) . $const->generate();
                }
            }

            $constants[] = '';
        }
        $body[] = implode("\n", $constants);

        // generate property
        $properties = [];
        if (count($this->properties) > 0) {
            foreach ($this->properties as $property) {
                /* @phpstan-ignore-next-line */
                if ($property instanceof Property) {
                    $property
                        ->tabSize($this->tabSize)
                        ->tabIndent($this->tabIndent);
                    $properties[] = $tabDept(1) . $property->generate();
                }
            }

            $properties[] = '';
        }
        $body[] = implode("\n", $properties);

        // generate functions
        $methods = [];
        if (count($this->methods) > 0) {
            foreach ($this->methods as $method) {
                /* @phpstan-ignore-next-line */
                if ($method instanceof Method) {
                    $method
                        ->tabSize($this->tabSize)
                        ->tabIndent($this->tabIndent);
                    $methods[] = $tabDept(1) . $method->generate();
                }
            }

            $methods[] = '';
        }
        $body[] = implode("\n\n", array_filter($methods));

        // generate raw body
        if (count($this->body) > 0) {
            $body[] = $tabDept(1) . implode("\n" . $tabDept(2), $this->body);
        }

        $body = implode("\n", array_filter($body));

        // end with new line
        $end = $this->endWithNewline ? "\n" : '';

        // manual replace
        $search  = $this->replace[0] ?? null;
        $replace = $this->replace[1] ?? null;

        return str_replace(
            ['{{before}}', '{{comment}}', '{{rule}}', '{{head}}', '{{body}}', '{{end}}', ...$search],
            [$before, $comment, $rule, $head, $body, $end, ...$replace],
            $class
        );
    }

    private function ruleText(): string
    {
        return match ($this->rule) {
            self::SET_CLASS, self::SET_ABSTRACT => 'abstract',
            self::SET_TRAIT => 'trait',
            default         => '',
        };
    }

    /**
     * @param string $pathToSave
     * @return int|false
     */
    public function save(string $pathToSave): int|false
    {
        return file_put_contents($pathToSave . '/' . $this->name . '.php', $this->generate());
    }

    // setter property

    public function rule(int $rule = self::SET_CLASS): self
    {
        $this->rule = $rule;

        return $this;
    }

    public function setFinal(bool $isFinal = true): self
    {
        $this->isFinal = $isFinal;

        return $this;
    }

    public function setEndWithNewLine(bool $enable = true): self
    {
        $this->endWithNewline = $enable;

        return $this;
    }

    // setter

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function namespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function use(string $useNamespace): self
    {
        $this->uses[] = $useNamespace;

        return $this;
    }

    /**
     * @param string[] $usesNamespace
     */
    public function uses(array $usesNamespace): self
    {
        $this->uses = $usesNamespace;

        return $this;
    }

    public function extend(string $extend): self
    {
        $this->extend = $extend;

        return $this;
    }

    public function implement(string $implement): self
    {
        $this->implements[] = $implement;

        return $this;
    }

    /**
     * @param string[] $implements
     */
    public function implements(array $implements): self
    {
        $this->implements = $implements;

        return $this;
    }

    public function trait(string $trait): self
    {
        $this->traits[] = $trait;

        return $this;
    }

    /**
     * @param string[] $traits
     */
    public function traits(array $traits): self
    {
        $this->traits = $traits;

        return $this;
    }

    public function body(string $rawBody): self
    {
        $this->body[] = $rawBody;

        return $this;
    }

    // setter - other
    public function addConst(string $name = 'NEW_CONST'): Constant
    {
        return $this->constants[] = new Constant($name);
    }

    /**
     * @param callable(ConstPool): void|Constant|ConstPool $newConst callable with param pools constants,
     *                                                                single constants or constPool
     */
    public function constants(callable|ConstPool|Constant $newConst): self
    {
        // detect if single const
        if ($newConst instanceof Constant) {
            $this->constants[] = $newConst;
        }

        // detect if multi const with constPool
        elseif (is_callable($newConst)) {
            $const = new ConstPool();

            call_user_func_array($newConst, [$const]);

            foreach ($const->getPools() as $pool) {
                if ($pool instanceof Constant) {
                    $this->constants[] = $pool;
                }
            }
        }

        // detect parameter is instance constPool
        elseif ($newConst instanceof ConstPool) {
            foreach ($newConst->getPools() as $pool) {
                if ($pool instanceof Constant) {
                    $this->constants[] = $pool;
                }
            }
        }

        return $this;
    }

    public function addProperty(string $name = 'new_property'): Property
    {
        return $this->properties[] = new Property($name);
    }

    /**
     * @param callable(PropertyPool): void|Property|PropertyPool $newProperty callable with param pools constants or
     *                                                                         single property
     */
    public function properties(callable|Property|PropertyPool $newProperty): self
    {
        // detect if single properties
        if ($newProperty instanceof Property) {
            $this->properties[] = $newProperty;
        }

        // detect if multi property with propertyPool
        elseif (is_callable($newProperty)) {
            $property = new PropertyPool();

            call_user_func_array($newProperty, [$property]);

            foreach ($property->getPools() as $pool) {
                if ($pool instanceof Property) {
                    $this->properties[] = $pool;
                }
            }
        }

        // detect parameter is instance methodPool
        elseif ($newProperty instanceof PropertyPool) {
            foreach ($newProperty->getPools() as $pool) {
                if ($pool instanceof Property) {
                    $this->properties[] = $pool;
                }
            }
        }

        return $this;
    }

    public function addMethod(string $name = 'new_method'): Method
    {
        return $this->methods[] = new Method($name);
    }

    /**
     * @param callable(MethodPool): void|Method|MethodPool $newMethod callable with param pools constants or
     *                                                                 single property
     */
    public function methods(MethodPool|callable|Method $newMethod): self
    {
        // detect if single properties
        if ($newMethod instanceof Method) {
            $this->methods[] = $newMethod;
        }

        // detect if multi property with methodsPool
        elseif (is_callable($newMethod)) {
            $method = new MethodPool();

            call_user_func_array($newMethod, [$method]);

            foreach ($method->getPools() as $pool) {
                if ($pool instanceof Method) {
                    $this->methods[] = $pool;
                }
            }
        }
        // detect parameter is instance methodPool
        elseif ($newMethod instanceof MethodPool) {
            foreach ($newMethod->getPools() as $pool) {
                if ($pool instanceof Method) {
                    $this->methods[] = $pool;
                }
            }
        }

        return $this;
    }

    /**
     * @param string|string[] $search  Text to replace
     * @param string|string[] $replace Text replacer
     */
    public function preReplace(array|string $search, array|string $replace): self
    {
        $search  = is_array($search) ? $search : [$search];
        $replace = is_array($replace) ? $replace : [$replace];

        $this->preReplace = [$search, $replace];

        return $this;
    }

    /**
     * @param string|string[] $search  Text to replace
     * @param string|string[] $replace Text replacer
     */
    public function replace(array|string $search, array|string $replace): self
    {
        $search  = is_array($search) ? $search : [$search];
        $replace = is_array($replace) ? $replace : [$replace];

        $this->replace = [$search, $replace];

        return $this;
    }
}
