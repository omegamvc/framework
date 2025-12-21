<?php

/**
 *
 */

/** @noinspection PhpDocSignatureInspection */

declare(strict_types=1);

namespace Omega\Database\Query\Traits;

use Omega\Database\Query\Select;

use function implode;

/**
 * Sub where query trait.
 */
trait SubQueryTrait
{
    /**
     * Add sub query to where statement.
     *
     * @param string $clause
     * @param Select $select
     * @return self
     */
    public function whereClause(string $clause, Select $select): self
    {
        $binds         = (fn () => $this->{'_binds'})->call($select);
        $this->where[] = implode(' ', [$clause, '(', (string) $select, ')']);
        foreach ($binds as $bind) {
            $this->binds[] = $bind;
        }

        return $this;
    }

    /**
     * @param string $columnName
     * @param string $operator
     * @param Select $select
     * @return self
     */
    public function whereCompare(string $columnName, string $operator, Select $select): self
    {
        return $this->whereClause($columnName . ' ' . $operator, $select);
    }

    /**
     * Added 'where exists' condition (query builder).
     *
     * @param Select $select Select class
     * @return self
     */
    public function whereExist(Select $select): self
    {
        return $this->whereClause('EXISTS', $select);
    }

    /**
     * Added 'where not exists' condition (query builder).
     *
     * @param Select $select Select class
     * @return self
     */
    public function whereNotExist(Select $select): self
    {
        return $this->whereClause('NOT EXISTS', $select);
    }

    /**
     * Added 'where equal' condition (query builder).
     *
     * @param string $columnName
     * @param Select $select
     * @return self
     */
    public function whereEqual(string $columnName, Select $select): self
    {
        return $this->whereClause($columnName . ' =', $select);
    }

    /**
     * Added 'where like' condition (query builder).
     *
     * @param string $columnName
     * @param Select $select
     * @return self
     */
    public function whereLike(string $columnName, Select $select): self
    {
        return $this->whereClause($columnName . ' LIKE', $select);
    }

    /**
     * Added 'where in' condition (query builder).
     *
     * @param string $columnName
     * @param Select $select
     * @return self
     */
    public function whereIn(string $columnName, Select $select): self
    {
        return $this->whereClause($columnName . ' IN', $select);
    }
}
