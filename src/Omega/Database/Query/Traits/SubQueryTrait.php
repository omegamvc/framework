<?php

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

namespace Omega\Database\Query\Traits;

use Omega\Database\Query\Delete;
use Omega\Database\Query\Select;
use Omega\Database\Query\Update;
use Omega\Database\Query\Where;

use function implode;

/**
 * Provides helper methods for adding subqueries to WHERE clauses
 * in SQL query builders like `Select`, `Update`, `Delete`, and `Where`.
 *
 * This trait simplifies the process of adding nested queries
 * using `EXISTS`, `NOT EXISTS`, `IN`, `=`, `LIKE`, etc., while
 * ensuring bind values are carried over from the subquery.
 *
 * Classes using this trait must expose a `$where` and `$binds` property.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Query\Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
trait SubQueryTrait
{
    /**
     * Add a raw subquery clause to the WHERE condition.
     *
     * @param string $clause The base clause to prefix the subquery (e.g., 'EXISTS', 'user_id IN').
     * @param Select $select The subquery (as a `Select` object).
     * @return Delete|Select|SubQueryTrait|Update|Where
     */
    public function whereClause(string $clause, Select $select): self
    {
        $binds          = (fn () => $this->{'binds'})->call($select);
        $this->where[] = implode(' ', [$clause, '(', (string) $select, ')']);
        foreach ($binds as $bind) {
            $this->binds[] = $bind;
        }

        return $this;
    }

    /**
     * Add a comparison-based subquery condition to WHERE (e.g., `user_id = (SELECT ...)`).
     *
     * @param string $column_name Column name on the outer query.
     * @param string $operator Comparison operator (e.g., '=', '>', '<').
     * @param Select $select Subquery to compare against.
     * @return Delete|Select|SubQueryTrait|Update|Where
     */
    public function whereCompare(string $column_name, string $operator, Select $select): self
    {
        return $this->whereClause($column_name . ' ' . $operator, $select);
    }

    /**
     * Add an `EXISTS (subquery)` condition to the WHERE clause.
     *
     * @param Select $select Subquery to check existence.
     * @return Delete|Select|SubQueryTrait|Update|Where
     */
    public function whereExist(Select $select): self
    {
        return $this->whereClause('EXISTS', $select);
    }

    /**
     * Add a `NOT EXISTS (subquery)` condition to the WHERE clause.
     *
     * @param Select $select Subquery to check non-existence.
     * @return Delete|Select|SubQueryTrait|Update|Where
     */
    public function whereNotExist(Select $select): self
    {
        return $this->whereClause('NOT EXISTS', $select);
    }

    /**
     * Add a `column = (subquery)` condition to the WHERE clause.
     *
     * @param string $columnName Column name to compare.
     * @param Select $select Subquery to compare against.
     * @return Delete|Select|SubQueryTrait|Update|Where
     */
    public function whereEqual(string $columnName, Select $select): self
    {
        return $this->whereClause($columnName . ' =', $select);
    }

    /**
     * Add a `column LIKE (subquery)` condition to the WHERE clause.
     *
     * @param string $columnName Column name to compare.
     * @param Select $select Subquery to compare against.
     * @return Delete|Select|SubQueryTrait|Update|Where
     */
    public function whereLike(string $columnName, Select $select): self
    {
        return $this->whereClause($columnName . ' LIKE', $select);
    }

    /**
     * Add a `column IN (subquery)` condition to the WHERE clause.
     *
     * @param string $columnName Column name to match.
     * @param Select $select Subquery to match against.
     * @return Delete|Select|SubQueryTrait|Update|Where
     */
    public function whereIn(string $columnName, Select $select): self
    {
        return $this->whereClause($columnName . ' IN', $select);
    }
}
