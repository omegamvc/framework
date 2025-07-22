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

use function array_chunk;
use function array_filter;
use function count;
use function implode;

/**
 * Handles SQL INSERT operations with support for single and multiple rows,
 * binding of values, and "ON DUPLICATE KEY UPDATE" clause.
 *
 * This class builds and executes parameterized INSERT statements
 * using a fluent interface and PDO bindings.
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
class Insert extends AbstractExecute
{
    /**
     * Columns and expressions used in ON DUPLICATE KEY UPDATE clause.
     *
     * The key is the column name, and the value is the expression to apply.
     * If no value is set, the default is to use VALUES(column).
     *
     * @var array<string, string>|null
     */
    private ?array $duplicateKey = null;

    /**
     * Create a new Insert query for a specific table.
     *
     * @param string     $tableName Table name to insert into
     * @param Connection $pdo       Database connection instance
     */
    public function __construct(string $tableName, Connection $pdo)
    {
        $this->table = $tableName;
        $this->pdo   = $pdo;
    }

    /**
     * Convert the built INSERT query to a string.
     *
     * @return string The full SQL INSERT query
     */
    public function __toString(): string
    {
        return $this->builder();
    }

    /**
     * Set a list of values to insert into the table.
     * This is a shorthand for calling `value()` repeatedly.
     *
     * @param array<string, string|int|bool|null> $values Key-value pairs to insert
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
     * Add a single value to insert.
     *
     * @param string               $bind   Column name / bind key
     * @param bool|int|string|null $value  Value to bind
     * @return self
     */
    public function value(string $bind, bool|int|string|null $value): self
    {
        $this->binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_');

        return $this;
    }

    /**
     * Insert multiple rows in a single query.
     *
     * @param array<int, array<string, string|int|bool|null>> $rows List of key-value pairs representing rows
     * @return self
     */
    public function rows(array $rows): self
    {
        foreach ($rows as $index => $values) {
            foreach ($values as $bind => $value) {
                $this->binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_' . $index . '_');
            }
        }

        return $this;
    }

    /**
     * Add a column to the ON DUPLICATE KEY UPDATE clause.
     *
     * If no custom value is provided, it defaults to using `VALUES(column)`.
     *
     * @param string      $column Column name to update
     * @param string|null $value  Expression to set (optional)
     * @return self
     */
    public function on(string $column, ?string $value = null): self
    {
        $this->duplicateKey[$column] = $value ?? "VALUES({$column})";

        return $this;
    }

    /**
     * Builds the final SQL INSERT query string.
     *
     * @return string The generated SQL INSERT statement
     */
    protected function builder(): string
    {
        [$binds, , $columns] = $this->bindsDestructor();

        $stringsBinds = [];
        /** @var array<int, array<int, string>> $chunk */
        $chunk = array_chunk($binds, count($columns), true);
        foreach ($chunk as $group) {
            $stringsBinds[] = '(' . implode(', ', $group) . ')';
        }

        $builds              = [];
        $builds['column']    = '(' . implode(', ', $columns) . ')';
        $builds['values']    = 'VALUES';
        $builds['binds']     = implode(', ', $stringsBinds);
        $builds['keyUpdate'] = $this->getDuplicateKeyUpdate();
        $string_build        = implode(' ', array_filter($builds, fn ($item) => $item !== ''));

        $this->query = "INSERT INTO {$this->table} {$string_build}";

        return $this->query;
    }

    /**
     * Builds the ON DUPLICATE KEY UPDATE part of the query, if defined.
     *
     * @return string SQL fragment for ON DUPLICATE KEY UPDATE
     */
    private function getDuplicateKeyUpdate(): string
    {
        if (null === $this->duplicateKey) {
            return '';
        }

        $keys = [];
        foreach ($this->duplicateKey as $key => $value) {
            $keys[] = "{$key} = {$value}";
        }

        return 'ON DUPLICATE KEY UPDATE ' . implode(', ', $keys);
    }
}
