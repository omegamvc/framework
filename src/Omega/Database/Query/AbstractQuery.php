<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\ConnectionInterface;

use function array_filter;
use function array_map;
use function implode;
use function in_array;
use function is_bool;
use function is_string;
use function str_contains;
use function str_replace;

abstract class AbstractQuery
{
    /** @var ConnectionInterface PDO property */
    protected ConnectionInterface $pdo;

    /** @var string Main query */
    protected string $query;

    /** @var string Table Name */
    protected string $table = '';

    protected ?InnerQuery $subQuery = null;

    /** @var string[] Columns name */
    protected array $column = ['*'];

    /**
     * Binder array(['key', 'val']).
     *
     * @var Bind[] Binder for PDO bind
     * @noinspection PhpGetterAndSetterCanBeReplacedWithPropertyHooksInspection
     */
    protected array $binds = [];

    /** @var int Limit start from */
    protected int $limitStart = 0;

    /** @var int Limit end to */
    protected int $limitEnd = 0;

    /** @var int offest */
    protected int $offset = 0;

    /** @var array<string, string> Sort result ASC|DESC */
    protected array $sortOrder  = [];

    public const int ORDER_ASC  = 0;
    public const int ORDER_DESC = 1;

    /**
     * Final where statement.
     *
     * @var string[]
     */
    protected array $where = [];

    /**
     * Grouping.
     *
     * @var string[]
     */
    protected array $groupBy = [];

    /**
     * Multi filter with strict mode.
     *
     * @var array<int, array<string, array<string, array<string, string>>>>
     */
    protected array $groupFilters = [];

    /**
     * Single filter and single strict mode.
     *
     * @var array<string, string>
     */
    protected array $filters = [];

    /**
     * Strict mode.
     *
     * @var bool True if you use AND instance of OR
     */
    protected bool $strictMode = true;

    /**
     * @var string[]
     */
    protected array $join = [];

    /**
     * reset all property.
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

    // Query builder

    /**
     * Get where statement base binding set before.
     *
     * @return string Where statement from binder
     */
    protected function getWhere(): string
    {
        $merging      = $this->mergeFilters();
        $where        = $this->splitGroupsFilters($merging);
        $glue         = $this->strictMode ? ' AND ' : ' OR ';
        $whereCustom  = implode($glue, $this->where);

        if ($where !== '' && $whereCustom !== '') {
            $whereString = $this->strictMode ? "AND $whereCustom" : "OR $whereCustom";

            return "WHERE $where $whereString";
        } elseif ($where === '' && $whereCustom !== '') {
            $whereString = $whereCustom;

            return "WHERE $whereString";
        } elseif ($where !== '') {
            return "WHERE $where";
        }

        // return condition where statement
        return $where;
    }

    /**
     * @return array<int, array<string, array<string, array<string, string>>>>
     */
    protected function mergeFilters(): array
    {
        $newGroupFilters = $this->groupFilters;
        if (!empty($this->filters)) {
            // merge group filter and main filter (condition)
            $newGroupFilters[] = [
                'filters' => $this->filters,
                'strict'  => $this->strictMode,
            ];
        }

        return $newGroupFilters;
    }

    /**
     * @param array<int, array<string, array<string, array<string, string>>>> $groupFilters Groups of filters
     * @return string
     */
    protected function splitGroupsFilters(array $groupFilters): string
    {
        $whereStatement = [];
        foreach ($groupFilters as $filters) {
            $single           = $this->splitFilters($filters);
            $whereStatement[] = "( $single )";
        }

        return implode(' AND ', $whereStatement);
    }

    /**
     * @param array<string, array<string, array<string, string>>> $filters Filters
     * @return string
     */
    protected function splitFilters(array $filters): string
    {
        $query     = [];
        $tableName = null === $this->subQuery ? $this->table : $this->subQuery->getAlias();
        foreach ($filters['filters'] as $fieldName => $fieldValue) {
            $value      = $fieldValue['value'];
            $comparison = $fieldValue['comparison'];
            $column     = str_contains($fieldName, '.') ? $fieldName : "{$tableName}.{$fieldName}";
            $bind       = $fieldValue['bind'];

            if ($value !== '') {
                $query[] = "({$column} {$comparison} :{$bind})";
            }
        }

        $clearQuery = array_filter($query);

        return $filters['strict']
            ? implode(' AND ', $clearQuery)
            : implode(' OR ', $clearQuery);
    }

    /**
     * Bind query with binding.
     *
     * @return string
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
     * @return string
     */
    protected function builder(): string
    {
        return '';
    }

    /**
     * @return array<int, string[]|bool[]>>
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
     * @return Bind[]
     */
    public function getBinds(): array
    {
        return $this->binds;
    }

    /**
     * Add where condition from where ref.
     *
     * @param Where|null $ref
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
