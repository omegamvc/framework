<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

/**
 * Part of Omega - Database Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\Connection;
use Omega\Database\Query\Join\AbstractJoin;
use Omega\Database\Query\Traits\ConditionTrait;
use Omega\Database\Query\Traits\SubQueryTrait;

use function array_filter;
use function array_merge;
use function count;
use function implode;

/**
 * SQL DELETE query builder and executor.
 *
 * This class is responsible for constructing and executing `DELETE` SQL statements.
 * It supports subqueries, conditional filtering, and complex JOIN operations.
 * If a table alias is used, standard condition bindings will be ignored in favor of subqueries.
 *
 * Inherits execution behavior from `AbstractExecute` and uses `ConditionTrait` and `SubQueryTrait`
 * to enhance where-clause and subquery capabilities.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Query
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Delete extends AbstractExecute
{
    use ConditionTrait;
    use SubQueryTrait;

    /**
     * Optional alias for the table used in the DELETE query.
     *
     * When an alias is set, conditions with bind values are ignored,
     * unless subqueries are used.
     *
     * @var string|null
     */
    protected ?string $alias = null;

    /**
     * Create a new DELETE query builder instance.
     *
     * @param string     $tableName Name of the table to delete from
     * @param Connection $pdo       Database connection instance
     */
    public function __construct(string $tableName, Connection $pdo)
    {
        $this->table = $tableName;
        $this->pdo   = $pdo;
    }

    /**
     * Return the raw SQL string when the object is used in a string context.
     *
     * @return string The full DELETE SQL query
     */
    public function __toString(): string
    {
        return $this->builder();
    }

    /**
     * Set an alias for the main table used in the DELETE query.
     *
     * If an alias is used, conditions with binding values will be ignored
     * unless subqueries are provided. Aliases are also respected in JOIN clauses.
     *
     * @param string $alias Alias name
     * @return self
     */
    public function alias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    /**
     * Add a JOIN clause to the DELETE query.
     *
     * Supported join types include:
     *  - INNER JOIN
     *  - LEFT JOIN
     *  - RIGHT JOIN
     *  - FULL OUTER JOIN
     *
     * If the join is based on a subquery, associated binds are extracted and merged.
     *
     * @param AbstractJoin $refTable The join clause
     * @return self
     */
    public function join(AbstractJoin $refTable): self
    {
        $table = $this->alias ?? $this->table;
        $refTable->table($table);

        $this->join[] = $refTable->stringJoin();
        $binds         = (fn () => $this->{'subQuery'})->call($refTable);

        if (null !== $binds) {
            $this->binds = array_merge($this->binds, $binds->getBind());
        }

        return $this;
    }

    /**
     * Get the compiled JOIN clause string.
     *
     * @return string The full JOIN part of the DELETE query
     */
    private function getJoin(): string
    {
        return 0 === count($this->join)
            ? ''
            : implode(' ', $this->join)
        ;
    }

    /**
     * Build the complete DELETE SQL query.
     *
     * Includes table name, optional alias, JOINs, and WHERE conditions.
     *
     * @return string The compiled DELETE SQL statement
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
