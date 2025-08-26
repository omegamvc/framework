<?php

declare(strict_types=1);

namespace Omega\Console\Style\Color;

abstract class AbstractColor implements RuleInterface
{
    private array $rules = [];

    public array $rule {
        get {
            return $this->rules;
        }
        set(array $value) {
            $this->rules = $value;
        }
    }
    /**
     * @param array<int, int> $rule
     */
    public function __construct(?array $rule = [])
    {
        if ($rule !== null) {
            $this->setRule($rule);
        }
    }

    public function getRule(): array
    {
        return $this->rules;
    }

    public function setRule(?array $rule = []): void
    {
        if ($rule !== null) {
            $this->rules = $rule;
        }
    }

    public function hasRule(int $rule): bool
    {
        return in_array($rule, $this->rules, true);
    }

    public function clearRule(): void
    {
        $this->rules = [];
    }

    public function rawRule(): string
    {
        return implode(';', $this->rules);
    }
}
