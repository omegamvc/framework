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
use function max;

/**
 * SQL SELECT query builder.
 *
 * This class constructs SQL SELECT statements with support for:
 * - JOINs
 * - WHERE conditions
 * - GROUP BY
 * - ORDER BY
 * - LIMIT/OFFSET
 * - Subqueries
 *
 * It builds the SQL dynamically and manages bindings for safe execution via prepared statements.
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
class Select extends AbstractFetch
{
    use ConditionTrait;
    use SubQueryTrait;

    /**
     * Constructor for the Select query.
     *
     * @param string|InnerQuery $tableName   The table name or a subquery
     * @param string[]          $columnsName List of columns to select
     * @param Connection        $pdo         Database connection
     * @param string[]|null     $options     Optional query overrides
     */
    public function __construct(
        string|InnerQuery $tableName,
        array $columnsName,
        Connection $pdo,
        ?array $options = null
    ) {
        $this->subQuery = $tableName instanceof InnerQuery ? $tableName : new InnerQuery(table: $tableName);
        $this->column   = $columnsName;
        $this->pdo      = $pdo;

        // inherit bind from sub query
        if ($tableName instanceof InnerQuery) {
            $this->binds = $tableName->getBind();
        }

        $column       = implode(', ', $columnsName);
        $this->query = $options['query'] ?? "SELECT {$column} FROM { $this->subQuery}";
    }

    /**
     * Cast the object to a string by returning the generated SQL.
     *
     * @return string The SQL query
     */
    public function __toString(): string
    {
        return $this->builder();
    }

    /**
     * Static constructor for fluent query creation.
     *
     * @param string     $tableName   The name of the table
     * @param string[]   $columnName  The columns to select
     * @param Connection $pdo         Database connection
     * @return Select
     */
    public static function from(string $tableName, array $columnName, Connection $pdo): Select
    {
        return new Select($tableName, $columnName, $pdo);
    }

    /**
     * Add a JOIN clause to the query.
     *
     * @param AbstractJoin $refTable Join statement
     * @return $this
     */
    public function join(AbstractJoin $refTable): self
    {
        // override master table
        $refTable->table($this->subQuery->getAlias());

        $this->join[] = $refTable->stringJoin();
        $binds        = (fn () => $this->{'subQuery'})->call($refTable);

        if (null !== $binds) {
            $this->binds = array_merge($this->binds, $binds->getBind());
        }

        return $this;
    }

    /**
     * Build JOIN clause string.
     *
     * @return string
     */
    private function joinBuilder(): string
    {
        return 0 === count($this->join)
            ? ''
            : implode(' ', $this->join)
        ;
    }

    /**
     * Set both limit and offset for result pagination.
     *
     * @param int $limitStart The number of rows to fetch
     * @param int $limitEnd   The limit end value
     * @return $this
     */
    public function limit(int $limitStart, int $limitEnd): self
    {
        $this->limitStart($limitStart);
        $this->limitEnd($limitEnd);

        return $this;
    }

    /**
     * Set the starting point of the limit.
     *
     * @param int $value Starting index (default is 0)
     * @return $this
     */
    public function limitStart(int $value): self
    {
        $this->limitStart = max($value, 0);

        return $this;
    }

    /**
     * Set the end limit value.
     *
     * @param int $value Number of rows to return (0 means unlimited)
     * @return $this
     */
    public function limitEnd(int $value): self
    {
        $this->limitEnd = max($value, 0);

        return $this;
    }

    /**
     * Set the offset for paginated results.
     *
     * @param int $value Offset value
     * @return $this
     */
    public function offset(int $value): self
    {
        $this->offset = max($value, 0);

        return $this;
    }

    /**
     * Set limit and offset for paginated results.
     *
     * @param int $limit  Number of rows to return
     * @param int $offset Offset of the first row
     * @return $this
     */
    public function limitOffset(int $limit, int $offset): self
    {
        return $this
            ->limitStart($limit)
            ->limitEnd(0)
            ->offset($offset);
    }

    /**
     * Add an ORDER BY clause to the query.
     *
     * @param string      $columnName Column to sort
     * @param int         $orderUsing Use Query::ORDER_ASC or Query::ORDER_DESC
     * @param string|null $belongTo   Optional table alias
     * @return $this
     */
    public function order(string $columnName, int $orderUsing = Query::ORDER_ASC, ?string $belongTo = null): self
    {
        $order = 0 === $orderUsing ? 'ASC' : 'DESC';
        $belongTo ??= null === $this->subQuery ? $this->table : $this->subQuery->getAlias();
        $res = "{$belongTo}.{$columnName}";

        $this->sortOrder[$res] = $order;

        return $this;
    }

    /**
     * Add ORDER BY with IS NOT NULL condition.
     *
     * @param string      $columnName Column to sort
     * @param int         $orderUsing Sort direction
     * @param string|null $belongTo   Optional table alias
     * @return $this
     */
    public function orderIfNotNull(string $columnName, int $orderUsing = Query::ORDER_ASC, ?string $belongTo = null): self
    {
        return $this->order("{$columnName} IS NOT NULL", $orderUsing, $belongTo);
    }

    /**
     * Add ORDER BY with IS NULL condition.
     *
     * @param string      $columnName Column to sort
     * @param int         $orderUsing Sort direction
     * @param string|null $belongTo   Optional table alias
     * @return $this
     */
    public function orderIfNull(string $columnName, int $orderUsing = Query::ORDER_ASC, ?string $belongTo = null): self
    {
        return $this->order("{$columnName} IS NULL", $orderUsing, $belongTo);
    }

    /**
     * Add a GROUP BY clause to the query.
     *
     * @param string ...$groups Columns to group by
     * @return $this
     */
    public function groupBy(string ...$groups): self
    {
        $this->groupBy = $groups;

        return $this;
    }

    /**
     * Build the full SQL SELECT query.
     *
     * @return string The generated SQL query
     */
    protected function builder(): string
    {
        $column = implode(', ', $this->column);

        $build = [];

        $build['join']       = $this->joinBuilder();
        $build['where']      = $this->getWhere();
        $build['group_by']   = $this->getGroupBy();
        $build['sort_order'] = $this->getOrderBy();
        $build['limit']      = $this->getLimit();

        $condition = implode(' ', array_filter($build, fn ($item) => $item !== ''));

        return $this->query = "SELECT {$column} FROM {$this->subQuery} {$condition}";
    }

    /**
     * Generate the SQL LIMIT clause.
     *
     * @return string
     */
    private function getLimit(): string
    {
        $limit = $this->limitEnd > 0 ? "LIMIT $this->limitEnd" : '';

        if ($this->limitStart === 0) {
            return $limit;
        }

        if ($this->limitEnd === 0 && $this->offset > 0) {
            return "LIMIT $this->limitStart OFFSET $this->offset";
        }

        return "LIMIT $this->limitStart, $this->limitEnd";
    }

    /**
     * Generate the SQL GROUP BY clause.
     *
     * @return string
     */
    private function getGroupBy(): string
    {
        if ([] === $this->groupBy) {
            return '';
        }

        $groupBy = implode(', ', $this->groupBy);

        return "GROUP BY {$groupBy}";
    }

    /**
     * Generate the SQL ORDER BY clause.
     *
     * @return string
     */
    private function getOrderBy(): string
    {
        if ([] === $this->sortOrder) {
            return '';
        }

        $orders = [];
        foreach ($this->sortOrder as $column => $order) {
            $orders[] = "{$column} {$order}";
        }

        $orders = implode(', ', $orders);

        return "ORDER BY {$orders}";
    }

    /**
     * Set limit, offset, and order from a reference source.
     *
     * @param int                   $limitStart
     * @param int                   $limitEnd
     * @param int                   $offset
     * @param array<string, string> $sortOrder
     * @return void
     */
    public function sortOrderRef(int $limitStart, int $limitEnd, int $offset, array $sortOrder): void
    {
        $this->limitStart = $limitStart;
        $this->limitEnd   = $limitEnd;
        $this->offset     = $offset;
        $this->sortOrder  = $sortOrder;
    }
}
