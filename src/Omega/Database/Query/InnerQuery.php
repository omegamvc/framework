<?php

declare(strict_types=1);

namespace Omega\Database\Query;

use function trim;

final readonly class InnerQuery implements \Stringable
{
    /**
     * @param Select|null $select
     * @param string      $table
     */
    public function __construct(private ?Select $select = null, private string $table = '')
    {
    }

    /**
     * @return bool
     */
    public function isSubQuery(): bool
    {
        return null !== $this->select;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->table;
    }

    /**
     * @return Bind[]
     */
    public function getBind(): array
    {
        return $this->select->getBinds();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->isSubQuery()
            ? '(' . trim((string) $this->select) . ') AS ' . $this->getAlias()
            : $this->getAlias();
    }
}
