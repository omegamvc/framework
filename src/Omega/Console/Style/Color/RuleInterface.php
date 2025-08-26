<?php

declare(strict_types=1);

namespace Omega\Console\Style\Color;

interface RuleInterface
{
    /**
     * @return array<int, int>
     */
    public function getRule(): array;

    public function setRule(?array $rule = []): void;

    public function hasRule(int $rule): bool;

    public function clearRule(): void;

    public function rawRule(): string;
}
