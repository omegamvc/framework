<?php

declare(strict_types=1);

namespace Omega\Router\Attribute\Route;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    /**
     * @var array{method: string[], expression: string}
     */
    public array $route;

    /**
     * @param string[] $method
     */
    public function __construct(array $method, string $expression)
    {
        $this->route = [
            'method'      => $method,
            'expression'  => $expression,
        ];
    }
}
