<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\ConnectionInterface;
use Omega\Database\Query\Join\AbstractJoin;
use Omega\Database\Query\Traits\ConditionTrait;
use Omega\Database\Query\Traits\SubQueryTrait;

use function array_filter;
use function array_merge;
use function count;
use function implode;

class Update extends AbstractExecute
{
    use ConditionTrait;
    use SubQueryTrait;

    /**
     * @param string              $tableName
     * @param ConnectionInterface $pdo
     */
    public function __construct(string $tableName, ConnectionInterface $pdo)
    {
        $this->table = $tableName;
        $this->pdo   = $pdo;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->builder();
    }

    /**
     * Insert set value (single).
     *
     * @param array<string, string|int|bool|null> $values Array of bing and value
     * @return self
     */
    public function values(array $values): self
    {
        foreach ($values as $key => $value) {
            $this->value($key, $value);
        }

        return $this;
    }

    /**
     * Insert set value (single).
     *
     * @param string               $bind  Pdo bind
     * @param string|int|bool|null $value Value of the bind
     * @return self
     */
    public function value(string $bind, string|int|bool|null $value): self
    {
        $this->binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_');

        return $this;
    }

    /**
     * Join statement:
     *  - inner join
     *  - left join
     *  - right join
     *  - full join.
     *
     * @param AbstractJoin $refTable
     * @return self
     */
    public function join(AbstractJoin $refTable): self
    {
        // override master table
        $refTable->table($this->table);

        $this->join[] = $refTable->stringJoin();
        $binds        = (fn () => $this->{'sub_query'})->call($refTable);

        if (null !== $binds) {
            $this->binds = array_merge($this->binds, $binds->getBind());
        }

        return $this;
    }

    /**
     * @return string
     */
    private function getJoin(): string
    {
        return 0 === count($this->join)
            ? ''
            : implode(' ', $this->join)
        ;
    }

    /**
     * @return string
     */
    protected function builder(): string
    {
        $setter = [];
        foreach ($this->binds as $bind) {
            if ($bind->hasColumName()) {
                $setter[] = $bind->getColumnName() . ' = ' . $bind->getBind();
            }
        }

        $build          = [];
        $build['join']  = $this->getJoin();
        $build[]        = 'SET ' . implode(', ', $setter);
        $build['where'] = $this->getWhere();

        $query_parts = implode(' ', array_filter($build, fn ($item) => $item !== ''));

        return $this->query = "UPDATE {$this->table} {$query_parts}";
    }
}
