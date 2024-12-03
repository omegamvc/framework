<?php

/**
 * Part of Omega CMS -  Database Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Database\QueryBuilder;

/*
 * @use
 */
use function array_map;
use function array_push;
use function count;
use function join;
use function is_array;
use function is_null;
use function is_string;
use Omega\Database\Adapter\DatabaseAdapterInterface;
use Omega\Database\Exception\QueryException;
use Pdo;
use PdoStatement;

/**
 * Abstract query builder class.
 *
 * The `AbstractQueryBuilder` class provides a foundation for building SQL queries
 * in an object-oriented manner. It supports various common SQL operations,
 * including SELECT, INSERT, UPDATE, and DELETE queries. *
 *
 * @category    Omega
 * @package     Database
 * @subpackage  QueryBuilder
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
abstract class AbstractQueryBuilder implements QueryBuilderInterface
{
    /**
     * Query type.
     *
     * @var string Holds the query type.
     */
    protected string $type;

    /**
     * Columns.
     *
     * @var array<string> Holds an array of columns.
     */
    protected array $columns;

    /**
     * Table name.
     *
     * @var string Holds the table name.
     */
    protected string $table;

    /**
     * Limit.
     *
     * @var int Holds the limit.
     */
    protected int $limit;

    /**
     * Offset.
     *
     * @var int Holds the offset.
     */
    protected int $offset;

    /**
     * Values array.
     *
     * @var array<mixed> Holds an array of values for the query.
     */
    protected array $values;

    /**
     * {@inheritdoc}
     *
     * @var array<array{string, string, mixed}> Holds an array of wheres clause.
     */
    protected array $wheres = [];

    /**
     * AbstractQueryBuilder class contructor.
     *
     * @param DatabaseAdapterInterface $connection Holds an instance of Mysql.
     *
     * @return void
     */
    public function __construct(
        protected DatabaseAdapterInterface $connection
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @return array<array<string, mixed>> Returns an array containing all rows in the database.
     */
    public function all(): array
    {
        if (! isset($this->type)) {
            $this->select();
        }

        $statement = $this->prepare();
        $statement->execute($this->getWhereValues());

        return $statement->fetchAll(Pdo::FETCH_ASSOC);
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed> Return an array of result based on where clause.
     */
    public function getWhereValues(): array
    {
        $values = [];

        if (count($this->wheres) === 0) {
            return $values;
        }

        foreach ($this->wheres as $where) {
            $values[$where[0]] = $where[2];
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     *
     * @return PdoStatement Return an instance of PdoStatement.
     *
     * @throws QueryException if unrecognized query type.
     */
    public function prepare(): PdoStatement
    {
        $query = '';

        if ($this->type === 'select') {
            $query = $this->compileSelect($query);
            $query = $this->compileWheres($query);
            $query = $this->compileLimit($query);
        }

        if ($this->type === 'insert') {
            $query = $this->compileInsert($query);
        }

        if ($this->type === 'update') {
            $query = $this->compileUpdate($query);
            $query = $this->compileWheres($query);
        }

        if ($this->type === 'delete') {
            $query = $this->compileDelete($query);
            $query = $this->compileWheres($query);
        }

        if (empty($query)) {
            throw new QueryException(
                'Unrecognised query type'
            );
        }

        return $this->connection->pdo()->prepare($query);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $query Holds the query data.
     *
     * @return string Return the compiled select clause.
     */
    public function compileSelect(string $query): string
    {
        $joinedColumns = join(', ', $this->columns);

        $query .= " SELECT {$joinedColumns} FROM {$this->table}";

        return $query;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $query Holds the query data.
     *
     * @return string Return the compiled limit clause.
     */
    public function compileLimit(string $query): string
    {
        if (isset($this->limit)) {
            $query .= " LIMIT {$this->limit}";
        }

        if (isset($this->offset)) {
            $query .= " OFFSET {$this->offset}";
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $query Holds the query data.
     *
     * @return string Return the compiled where clause.
     */
    public function compileWheres(string $query): string
    {
        if (count($this->wheres) === 0) {
            return $query;
        }

        $query .= ' WHERE';

        foreach ($this->wheres as $i => $where) {
            if ($i > 0) {
                $query .= ' AND ';
            }

            [ $column, $comparator, $value ] = $where;

            $query .= " {$column} {$comparator} :{$column}";
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $query Holds the query data.
     *
     * @return string Return the compiled insert clause.
     */
    public function compileInsert(string $query): string
    {
        $joinedColumns      = join(', ', $this->columns);
        $joinedPlaceholders = join(', ', array_map(fn($column) => ":{$column}", $this->columns));

        $query .= " INSERT INTO {$this->table} ({$joinedColumns}) VALUES ({$joinedPlaceholders})";

        return $query;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $query Holds the query data.
     *
     * @return string Return the compiled update clause.
     */
    public function compileUpdate(string $query): string
    {
        $joinedColumns = '';

        foreach ($this->columns as $i => $column) {
            if ($i > 0) {
                $joinedColumns .= ', ';
            }

            $joinedColumns .= " {$column} = :{$column}";
        }

        $query .= " UPDATE {$this->table} SET {$joinedColumns}";

        return $query;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $query Holds the query data.
     *
     * @return string Return the compiled delete clause.
     */
    public function compileDelete(string $query): string
    {
        $query .= " DELETE FROM {$this->table}";

        return $query;
    }

    /**
     * {@inheritdoc}
     *
     * @return ?array<string, mixed> Returns an array with the first row or null if no rows are found.
     */
    public function first(): ?array
    {
        if (! isset($this->type)) {
            $this->select();
        }

        $statement = $this->take(1)->prepare();
        $statement->execute($this->getWhereValues());

        $result = $statement->fetchAll(Pdo::FETCH_ASSOC);

        if (count($result) === 1) {
            return $result[0];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @param int $limit  The limit for the set of query results.
     * @param int $offset The offset.
     *
     * @return $this
     */
    public function take(int $limit, int $offset = 0): static
    {
        $this->limit  = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $table Holds the table name.
     *
     * @return $this
     */
    public function from(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $columns Holds the column name.
     *
     * @return $this
     *
     * @throws QueryException if the columns is not a string or an arrya of string.
     */
    public function select(mixed $columns = '*'): static
    {
        if (is_string($columns)) {
            $columns = [ $columns ];
        } elseif (! is_array($columns)) {
            throw new QueryException(
                'Expected string or array of strings for columns.'
            );
        }

        // Ensure that $columns contains only strings
        foreach ($columns as $column) {
            if (! is_string($column)) {
                throw new QueryException(
                    'All columns must be strings.'
                );
            }
        }

        $this->type    = 'select';
        $this->columns = $columns;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param array<string> $columns Holds an array of columns.
     * @param array<mixed>  $values  Holds an array of values.
     *
     * @return int|bool Return the number of affected rows.
     */
    public function insert(array $columns, array $values): int|bool
    {
        $this->type    = 'insert';
        $this->columns = $columns;
        $this->values  = $values;

        $statement = $this->prepare();

        return $statement->execute($values);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $column     Holds the column name.
     * @param mixed  $comparator Holds the column comparator.
     * @param mixed  $value      Holds the data to store or null.
     *
     * @return $this
     */
    public function where(string $column, mixed $comparator, mixed $value = null): static
    {
        if (is_null($value) && ! is_null($comparator)) {
            array_push($this->wheres, [ $column, '=', $comparator ]);
        } else {
            array_push($this->wheres, [ $column, $comparator, $value ]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param array<string> $columns Holds an array of columns.
     * @param array<mixed>  $values  Holds an array of values.
     *
     * @return int|bool Return the number of affected rows.
     */
    public function update(array $columns, array $values): int|bool
    {
        $this->type    = 'update';
        $this->columns = $columns;
        $this->values  = $values;

        $statement = $this->prepare();

        return $statement->execute($this->getWhereValues() + $values);
    }

    /**
     * {@inheritdoc}
     *
     * @return string|false Return the last insert id.
     */
    public function getLastInsertId(): string|false
    {
        return $this->connection->pdo()->lastInsertId();
    }

    /**
     * {@inheritdoc}
     *
     * @return int|bool
     */
    public function delete(): int|bool
    {
        $this->type = 'delete';

        $statement = $this->prepare();

        return $statement->execute($this->getWhereValues());
    }
}
