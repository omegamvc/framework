<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\ConnectionInterface;
use Omega\Database\Query\Join\AbstractJoin;
use Omega\Database\Query\Traits\ConditionTrait;
use Omega\Database\Query\Traits\SubQueryTrait;

use function array_merge;
use function count;
use function implode;

class Delete extends AbstractExecute
{
    use ConditionTrait;
    use SubQueryTrait;

    /** @var string|null  */
    protected ?string $alias = null;

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
     * Set alias for the table.
     * If using an alias, conditions with binding values will be ignored,
     * except when using subqueries, clause in join also will be generated as alias.
     *
     * @param string $alias
     * @return self
     */
    public function alias(string $alias): self
    {
        $this->alias = $alias;

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
        $table = $this->alias ?? $this->table;
        $refTable->table($table);

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
        return 0 === count($this->join) ? '' : implode(' ', $this->join);
    }

    /**
     * @return string
     */
    protected function builder(): string
    {
        $build = [];

        $build['join']  = $this->getJoin();
        $build['where'] = $this->getWhere();

        $queryParts = implode(' ', array_filter($build, fn ($item) => $item !== ''));

        return $this->query =  null === $this->alias
            ? "DELETE FROM {$this->table} {$queryParts}"
            : "DELETE {$this->alias} FROM {$this->table} AS {$this->alias} {$queryParts}";
    }
}
