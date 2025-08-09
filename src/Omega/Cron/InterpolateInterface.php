<?php

declare(strict_types=1);

namespace Omega\Cron;

interface InterpolateInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function interpolate(string $message, array $context = []): void;
}
