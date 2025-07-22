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

use function array_filter;
use function array_map;
use function implode;
use function in_array;
use function is_bool;
use function is_string;
use function str_contains;
use function str_replace;

/**
 * Base abstract class for building SQL queries.
 *
 * This class provides shared functionality for various query types such as SELECT, INSERT,
 * UPDATE, and DELETE. It includes property management for bindings, filtering, joins,
 * sorting, grouping, pagination, and query construction. Subclasses must implement the
 * `builder()` method to generate specific query strings.
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
abstract class AbstractQuery
{
    /**
     * Value representing ascending sort order.
     *
     * @var int
     */
    public const int ORDER_ASC = 0;

    /**
     * Value representing descending sort order.
     *
     * @var int
     */
    public const int ORDER_DESC = 1;

    /**
     * PDO connection instance used for executing queries.
     *
     * @var Connection
     */
    protected Connection $pdo;

    /**
     * Final SQL query string after being built.
     *
     * @var string
     */
    protected string $query;

    /**
     * The name of the main table being queried.
     *
     * @var string
     */
    protected string $table = '';

    /**
     * Optional subquery to use instead of a direct table name.
     *
     * @var InnerQuery|null
     */
    protected ?InnerQuery $subQuery = null;

    /**
     * List of columns to select.
     *
     * @var string[]
     */
    protected array $column = ['*'];

    /**
     * Array of Bind objects for PDO parameter binding.
     *
     * @var Bind[]
     */
    protected array $binds = [];

    /**
     * Starting limit offset for pagination.
     *
     * @var int
     */
    protected int $limitStart = 0;

    /**
     * Ending limit count for pagination.
     *
     * @var int
     */
    protected int $limitEnd = 0;

    /**
     * Number of rows to skip before starting to return results.
     *
     * @var int
     */
    protected int $offset = 0;

    /**
     * Sort instructions by column name, e.g. ['users.name' => 'ASC'].
     *
     * @var array<string, string>
     */
    protected array $sortOrder = [];

    /**
     * Array of raw WHERE conditions as strings.
     *
     * @var string[]
     */
    protected array $where = [];

    /**
     * List of GROUP BY column names.
     *
     * @var string[]
     */
    protected array $groupBy = [];

    /**
     * Grouped filter conditions with strict (AND) or non-strict (OR) logic.
     *
     * @var array<int, array<string, array<string, array<string, string>>>>
     */
    protected array $groupFilters = [];

    /**
     * Single filter conditions to apply directly.
     *
     * @var array<string, string>
     */
    protected array $filters = [];

    /**
     * Whether to apply strict mode (AND logic) to filters.
     *
     * @var bool
     */
    protected bool $strictMode = true;

    /**
     * Array of join clauses to append to the query.
     *
     * @var string[]
     */
    protected array $join = [];

    /**
     * Reset all internal query parameters to their default values.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->table        = '';
        $this->subQuery     = null;
        $this->column       = ['*'];
        $this->binds        = [];
        $this->limitStart   = 0;
        $this->limitEnd     = 0;
        $this->where        = [];
        $this->groupFilters = [];
        $this->filters      = [];
        $this->strictMode   = true;

        return $this;
    }

    /**
     * Build and return the full WHERE clause string based on filters and custom conditions.
     *
     * @return string WHERE SQL fragment
     */
    protected function getWhere(): string
    {
        $merging     = $this->mergeFilters();
        $where       = $this->splitGroupsFilters($merging);
        $glue        = $this->strictMode ? ' AND ' : ' OR ';
        $whereCustom = implode($glue, $this->where);

        if ($where !== '' && $whereCustom !== '') {
            $whereString = $this->strictMode ? "AND $whereCustom" : "OR $whereCustom";

            return "WHERE $where $whereString";
        } elseif ($where === '' && $whereCustom !== '') {
            $whereString = $this->strictMode ? "$whereCustom" : "$whereCustom";

            return "WHERE $whereString";
        } elseif ($where !== '') {
            return "WHERE $where";
        }

        return $where;
    }

    /**
     * Merge all filters and grouped filters into a single array.
     *
     * If single filters exist, they are appended to the grouped filters list
     * under the current strict mode flag.
     *
     * @return array<int, array<string, array<string, array<string, string>>>>
     */
    protected function mergeFilters(): array
    {
        $newGroupFilters = $this->groupFilters;
        if (!empty($this->filters)) {
            $newGroupFilters[] = [
                'filters' => $this->filters,
                'strict'  => $this->strictMode,
            ];
        }

        return $newGroupFilters;
    }

    /**
     * Generate a composite WHERE clause string by joining grouped filters with AND logic.
     *
     * @param array<int, array<string, array<string, array<string, string>>>> $groupFilters
     * @return string
     */
    protected function splitGroupsFilters(array $groupFilters): string
    {
        $whereStatement = [];
        foreach ($groupFilters as $filters) {
            $single          = $this->splitFilters($filters);
            $whereStatement[] = "( $single )";
        }

        return implode(' AND ', $whereStatement);
    }

    /**
     * Generate a string representation of individual filter expressions
     * joined by AND/OR based on the strict flag.
     *
     * @param array<string, array<string, array<string, string>>> $filters
     * @return string
     */
    protected function splitFilters(array $filters): string
    {
        $query      = [];
        $tableName = null === $this->subQuery ? $this->table : $this->subQuery->getAlias();
        foreach ($filters['filters'] as $fieldName => $fieldValue) {
            $value        = $fieldValue['value'];
            $comparison   = $fieldValue['comparison'];
            $column       = str_contains($fieldName, '.') ? $fieldName : "{$tableName}.{$fieldName}";
            $bind         = $fieldValue['bind'];

            if ($value !== '') {
                $query[] = "({$column} {$comparison} :{$bind})";
            }
        }

        $clearQuery = array_filter($query);

        return $filters['strict'] ? implode(' AND ', $clearQuery) : implode(' OR ', $clearQuery);
    }

    /**
     * Return the complete query string with bind parameters replaced by their values.
     *
     * Primarily used for debugging or logging purposes.
     *
     * @return string Query string with values injected
     */
    public function queryBind(): string
    {
        [$binds, $values] = $this->bindsDestructor();

        $quoteValues = array_map(function ($value) {
            if (is_string($value)) {
                return "'" . $value . "'";
            }

            if (is_bool($value)) {
                if ($value === true) {
                    return 'true';
                }

                return 'false';
            }

            /* @phpstan-ignore-next-line */
            return $value;
        }, $values);

        return str_replace($binds, $quoteValues, $this->builder());
    }

    /**
     * Abstract builder method. Must be implemented by subclasses to build specific SQL queries.
     *
     * @return string
     */
    protected function builder(): string
    {
        return '';
    }

    /**
     * Deconstructs the bind array into an array of bind names, values, and involved columns.
     *
     * @return array{0: string[], 1: array<int|string|bool|null>, 2: string[]}
     */
    public function bindsDestructor(): array
    {
        $bindName = [];
        $value    = [];
        $columns  = [];

        foreach ($this->binds as $bind) {
            $bindName[] = $bind->getBind();
            $value[]    = $bind->getValue();
            if (!in_array($bind->getColumnName(), $columns)) {
                $columns[] = $bind->getColumnName();
            }
        }

        return [$bindName, $value, $columns];
    }

    /**
     * Retrieve all bind objects currently used in the query.
     *
     * @return Bind[]
     */
    public function getBinds(): array
    {
        return $this->binds;
    }

    /**
     * Import WHERE conditions, binds, filter, and strict mode from another Where object.
     *
     * Useful for reusing prebuilt query fragments.
     *
     * @param Where|null $ref Instance containing WHERE conditions
     * @return static
     */
    public function whereRef(?Where $ref): static
    {
        if ($ref->isEmpty()) {
            return $this;
        }
        $condition = $ref->get();
        foreach ($condition['binds'] as $bind) {
            $this->binds[] = $bind;
        }
        foreach ($condition['where'] as $where) {
            $this->where[] = $where;
        }
        foreach ($condition['filters'] as $name => $filter) {
            $this->filters[$name] = $filter;
        }
        $this->strictMode = $condition['isStrict'];

        return $this;
    }
}
