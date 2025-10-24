<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Traits;

trait ConditionTrait
{
    /** @var string */
    private string $ifExists = '';

    /**
     * @param bool $value
     * @return self
     */
    public function ifExists(bool $value = true): self
    {
        $this->ifExists = $value
            ? 'IF EXISTS'
            : 'IF NOT EXISTS'
        ;

        return $this;
    }

    /**
     * @param bool $value
     * @return self
     */
    public function ifNotExists(bool $value = true): self
    {
        $this->ifExists = $value
            ? 'IF NOT EXISTS'
            : 'IF EXISTS'
        ;

        return $this;
    }
}
