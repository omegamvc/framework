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

namespace Omega\Database\Query\Join;

use Omega\Database\Query\InnerQuery;

/**
 * Abstract base class to build SQL JOIN clauses between tables or subqueries.
 *
 * It supports defining relationships between a main table and a reference table,
 * including comparisons between columns, and optionally allows subqueries as join targets.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Query\Join
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
abstract class AbstractJoin
{
    /** @var string The name of the main (master) table initiating the join. */
    protected string $mainTable = '';

    /** @var string The name of the reference table or alias of the subquery being joined. */
    protected string $tableName = '';

    /** @var string Column from the main table used for join comparison. */
    protected string $columnName = '';

    /**
     * Array of column pairs used for comparison in the join condition.
     * Each element is an array with [mainTableColumn, refTableColumn].
     *
     * @var string[][]
     */
    protected array $compareColumn = [];

    /** @var string The raw SQL join string built by the join builder. */
    protected string $stringJoin = '';

    /** @var InnerQuery|null Optional subquery used as the reference join target. */
    protected ?InnerQuery $subQuery = null;

    /**
     * Final constructor, not extendable.
     *
     * @return void
     */
    final public function __construct()
    {
    }

    /**
     * Set the main table using an invokable interface.
     *
     * @param string $mainTable The name of the main table.
     * @return self
     */
    public function __invoke(string $mainTable): self
    {
        $this->mainTable = $mainTable;

        return $this;
    }

    /**
     * Converts the join object to string using the join builder.
     *
     * @return string The generated JOIN clause.
     */
    public function __toString(): string
    {
        return $this->stringJoin();
    }

    /**
     * Creates a new join instance from a table or subquery reference.
     *
     * @param string|InnerQuery $refTable The reference table or subquery.
     * @param string            $id       Column name in the main table.
     * @param string|null       $refId    Column name in the ref table (defaults to $id).
     * @return AbstractJoin The join instance.
     */
    public static function ref(string|InnerQuery $refTable, string $id, ?string $refId = null): AbstractJoin
    {
        $instance = new static();

        if ($refTable instanceof InnerQuery) {
            return $instance
                ->clause($refTable)
                ->compare($id, $refId);
        }

        return $instance
            ->tableRef($refTable)
            ->compare($id, $refId);
    }

    /**
     * Sets the main table name.
     *
     * @param string $mainTable The name of the main table.
     * @return self
     */
    public function table(string $mainTable): self
    {
        $this->mainTable = $mainTable;

        return $this;
    }

    /**
     * Defines a subquery as the join target.
     *
     * @param InnerQuery $select The subquery object.
     * @return $this
     */
    public function clause(InnerQuery $select): self
    {
        $this->subQuery  = $select;
        $this->tableName = $select->getAlias();

        return $this;
    }

    /**
     * Sets the name of the reference table.
     *
     * @param string $refTable The name of the reference table.
     * @return self
     */
    public function tableRef(string $refTable): self
    {
        $this->tableName = $refTable;

        return $this;
    }

    /**
     * Sets both main and reference table names.
     *
     * @param string $mainTable The main table name.
     * @param string $refTable  The reference table name.
     * @return self
     */
    public function tableRelation(string $mainTable, string $refTable): self
    {
        $this->mainTable = $mainTable;
        $this->tableName = $refTable;

        return $this;
    }

    /**
     * Adds a pair of columns to be compared in the join condition.
     *
     * @param string      $mainColumn    Column from the main table.
     * @param string|null $compareColumn Column from the ref table (defaults to $mainColumn).
     * @return self
     */
    public function compare(string $mainColumn, ?string $compareColumn = null): self
    {
        $compareColumn ??= $mainColumn;

        $this->compareColumn[] = [
            $mainColumn, $compareColumn,
        ];

        return $this;
    }

    /**
     * Returns the generated JOIN clause as a string.
     *
     * @return string Raw JOIN string.
     */
    public function stringJoin(): string
    {
        return $this->joinBuilder();
    }

    /**
     * Builds the raw JOIN clause string.
     *
     * @return string JOIN clause.
     */
    protected function joinBuilder(): string
    {
        return $this->stringJoin;
    }

    /**
     * Returns the ON clause condition string, combining column comparisons.
     *
     * @return string The ON clause, e.g., "a.id = b.user_id AND a.status = b.status".
     */
    protected function splitJoin(): string
    {
        $on = [];
        foreach ($this->compareColumn as $column) {
            $masterColumn  = $column[0];
            $compareColumn = $column[1];

            $on[] = "$this->mainTable.$masterColumn = $this->tableName.$compareColumn";
        }

        return implode(' AND ', $on);
    }

    /**
     * Gets the alias name for the reference table or subquery.
     *
     * @return string The alias name.
     */
    protected function getAlias(): string
    {
        return null === $this->subQuery ? $this->tableName : (string) $this->subQuery;
    }
}
