<?php

/** @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection */

/**
 * Part of Omega -  Database Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\QueryBuilder;

use PdoStatement;
use Omega\Database\Exception\QueryException;

/**
 * Abstract query builder class.
 *
 * The `AbstractQueryBuilder` class provides a foundation for building SQL queries
 * in an object-oriented manner. It supports various common SQL operations,
 * including SELECT, INSERT, UPDATE, and DELETE queries. *
 *
 * @category   Omega
 * @package    Database
 * @subpackage QueryBuilder
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface QueryBuilderInterface
{
    /**
     * Fetch all rows matching the current query.
     *
     * @return array<array<string, mixed>> Returns an array containing all rows in the database.
     */
    public function all(): array;

    /**
     * Get the values for the where clause placeholders.
     *
     * @return array<string, mixed> Return an array of result based on where clause.
     */
    public function getWhereValues(): array;

    /**
     * Prepare a query against a particular connection.
     *
     * @return PdoStatement Return an instance of PdoStatement.
     * @throws QueryException if unrecognized query type.
     */
    public function prepare(): PdoStatement;

    /**
     * Add select clause to the query.
     *
     * @param string $query Holds the query data.
     * @return string Return the compiled select clause.
     */
    public function compileSelect(string $query): string;

    /**
     * Add limit and offset clauses to the query.
     *
     * @param string $query Holds the query data.
     * @return string Return the compiled limit clause.
     */
    public function compileLimit(string $query): string;

    /**
     * Add where clauses to the query.
     *
     * @param string $query Holds the query data.
     * @return string Return the compiled where clause.
     */
    public function compileWheres(string $query): string;

    /**
     * Add insert clause to the query.
     *
     * @param string $query Holds the query data.
     * @return string Return the compiled insert clause.
     */
    public function compileInsert(string $query): string;

    /**
     * Add update clause to the query.
     *
     * @param string $query Holds the query data.
     * @return string Return the compiled update clause.
     */
    public function compileUpdate(string $query): string;

    /**
     * Add delete clause to the query.
     *
     * @param string $query Holds the query data.
     * @return string Return the compiled delete clause.
     */
    public function compileDelete(string $query): string;

    /**
     * Fetch the first row matching the current query.
     *
     * @return ?array<string, mixed> Returns an array with the first row or null if no rows are found.
     */
    public function first(): ?array;

    /**
     * Limit a set of query results to fetch a single or a limited batch of rows.
     *
     * @param int $limit  The limit for the set of query results.
     * @param int $offset The offset.
     * @return $this
     */
    public function take(int $limit, int $offset = 0): static;

    /**
     * Indicate which table the query is targeting.
     *
     * @param string $table Holds the table name.
     * @return $this
     */
    public function from(string $table): static;

    /**
     * Indicate the query type is a "select" and remember
     * which fields should be returned by the query.
     *
     * @param mixed $columns Holds the column name.
     * @return $this
     */
    public function select(mixed $columns = '*'): static;

    /**
     * Insert a row of data into the table specified in the query
     * and return the number of affected rows.
     *
     * @param array<string> $columns Holds an array of columns.
     * @param array<mixed>  $values  Holds an array of values.
     * @return int|bool Return the number of affected rows.
     */
    public function insert(array $columns, array $values): int|bool;

    /**
     * Store where clause data for later queries.
     *
     * @param string $column     Holds the column name.
     * @param mixed  $comparator Holds the column comparator.
     * @param mixed  $value      Holds the data to store or null.
     * @return $this
     */
    public function where(string $column, mixed $comparator, mixed $value = null): static;

    /**
     * Insert a row of data into the table specified in the query
     * and return the number of affected rows.
     *
     * @param array<string> $columns Holds an array of columns.
     * @param array<mixed>  $values  Holds an array of values.
     * @return int|bool Return the number of affected rows.
     */
    public function update(array $columns, array $values): int|bool;

    /**
     * Get the ID of the last row that was inserted.
     *
     * @return string|false Return the last insert id.
     */
    public function getLastInsertId(): string|false;

    /**
     * Delete a row from the database.
     *
     * @return int|bool
     */
    public function delete(): int|bool;
}
