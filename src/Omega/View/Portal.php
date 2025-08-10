<?php

declare(strict_types=1);

namespace Omega\View;

readonly class Portal
{
    /**
     * Set portal items.
     *
     * @param array<string, mixed> $items
     */
    public function __construct(private array $items)
    {
    }

    /**
     * Get property value.
     *
     * @param string $name Property name
     * @return mixed Property value, null if not found     *
     */
    public function __get(string $name): mixed
    {
        return $this->items[$name] ?? null;
    }

    /**
     * Check property has exists or not.
     *
     * @param string $name Property name
     * @return bool True if property name exists
     */
    public function has(string $name): bool
    {
        return isset($this->items[$name]);
    }
}
