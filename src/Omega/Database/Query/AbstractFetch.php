<?php

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Collection\Collection;

abstract class AbstractFetch extends AbstractQuery
{
    /**
     * @return Collection<string|int, mixed>|null
     */
    public function get(): ?Collection
    {
        if (false === ($items = $this->all())) {
            $items = [];
        }

        return new Collection($items);
    }

    /**
     * @return string[]
     */
    public function single(): array
    {
        $this->builder();

        $this->pdo->query($this->query);
        foreach ($this->binds as $bind) {
            if (!$bind->hasBind()) {
                $this->pdo->bind($bind->getBind(), $bind->getValue());
            }
        }
        $result = $this->pdo->single();

        return $result === false ? [] : $this->pdo->single();
    }

    /** @return array<string|int, mixed>|false */
    public function all(): array|false
    {
        $this->builder();

        $this->pdo->query($this->query);
        foreach ($this->binds as $bind) {
            if (!$bind->hasBind()) {
                $this->pdo->bind($bind->getBind(), $bind->getValue());
            }
        }

        return $this->pdo->resultset();
    }
}
