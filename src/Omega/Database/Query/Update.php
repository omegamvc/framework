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
use function implode;

/**
 * Update query builder.
 *
 * This class constructs and executes SQL UPDATE statements using a fluent interface.
 * It supports setting values, joining tables, and applying conditional logic through traits.
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
class Update extends AbstractExecute
{
    use ConditionTrait;
    use SubQueryTrait;

    /**
     * Initialize a new Update query for the specified table.
     *
     * @param string     $tableName The name of the table to update
     * @param Connection $pdo       The PDO connection instance
     */
    public function __construct(string $tableName, Connection $pdo)
    {
        $this->table = $tableName;
        $this->pdo   = $pdo;
    }

    /**
     * Cast the query to string.
     *
     * @return string The built SQL update query
     */
    public function __toString(): string
    {
        return $this->builder();
    }

    /**
     * Set multiple column values to update.
     *
     * @param array<string, string|int|bool|null> $values An associative array of column => value
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
     * Set a single column value to update.
     *
     * @param string               $bind  The column name
     * @param bool|int|string|null $value The value to bind
     * @return self
     */
    public function value(string $bind, bool|int|string|null $value): self
    {
        $this->binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_');

        return $this;
    }

    /**
     * Add a JOIN clause to the update statement.
     *
     * Supported types:
     * - INNER JOIN
     * - LEFT JOIN
     * - RIGHT JOIN
     * - FULL JOIN
     *
     * @param AbstractJoin $refTable The join configuration
     * @return self
     */
    public function join(AbstractJoin $refTable): self
    {
        // Set the base table name in the join
        $refTable->table($this->table);

        $this->join[] = $refTable->stringJoin();

        // Access subquery (if any) from the join using closure
        $binds = (fn () => $this->{'subQuery'})->call($refTable);

        if (null !== $binds) {
            $this->binds = array_merge($this->binds, $binds->getBind());
        }

        return $this;
    }

    /**
     * Generate the SQL JOIN clause string.
     *
     * @return string The JOIN portion of the SQL query
     */
    private function getJoin(): string
    {
        return 0 === count($this->join)
            ? ''
            : implode(' ', $this->join);
    }

    /**
     * Build the final SQL UPDATE query.
     *
     * @return string The full SQL UPDATE statement
     */
    protected function builder(): string
    {
        $setter = [];
        foreach ($this->binds as $bind) {
            if ($bind->hasColumName()) {
                $setter[] = $bind->getColumnName() . ' = ' . $bind->getBind();
            }
        }

        $build = [];
        $build['join']  = $this->getJoin();
        $build[]        = 'SET ' . implode(', ', $setter);
        $build['where'] = $this->getWhere();

        $queryParts = implode(' ', array_filter($build, fn ($item) => $item !== ''));

        return $this->query = "UPDATE {$this->table} {$queryParts}";
    }
}
