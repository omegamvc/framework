<?php

/** @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection */

/**
 * Part of Omega -  Database Package.
 * PHP VERSION 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\QueryBuilder;

use Pdo;
use PdoStatement;
use Omega\Database\Adapter\DatabaseAdapterInterface;
use Omega\Database\Exception\QueryException;

use function array_map;
use function count;
use function join;
use function is_array;
use function is_null;
use function is_string;

/**
 * Abstract query builder class.
 *
 * The `AbstractQueryBuilder` class provides a foundation for building SQL queries
 * in an object-oriented manner. It supports various common SQL operations,
 * including SELECT, INSERT, UPDATE, and DELETE queries. *
 *
 * @category   Omega
 * @package    Database
 * @subpackage Migration
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
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
     * Array of wheres clause.
     *
     * @var array<array{string, string, mixed}> Holds an array of wheres clause.
     */
    protected array $wheres = [];

    /**
     * AbstractQueryBuilder class contractor.
     *
     * @param DatabaseAdapterInterface $connection Holds an instance of Mysql.
     * @return void
     */
    public function __construct(
        protected DatabaseAdapterInterface $connection
    ) {
    }

    /**
     * {@inheritdoc}
     * @throws QueryException
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
     */
    public function compileSelect(string $query): string
    {
        $joinedColumns = join(', ', $this->columns);

        $query .= " SELECT {$joinedColumns} FROM {$this->table}";

        return $query;
    }

    /**
     * {@inheritdoc}
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
     */
    public function compileDelete(string $query): string
    {
        $query .= " DELETE FROM {$this->table}";

        return $query;
    }

    /**
     * {@inheritdoc}
     * @throws QueryException
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
     */
    public function take(int $limit, int $offset = 0): static
    {
        $this->limit  = $limit;
        $this->offset = $offset;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function from(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws QueryException
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
     * @throws QueryException
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
     */
    public function where(string $column, mixed $comparator, mixed $value = null): static
    {
        if (is_null($value) && ! is_null($comparator)) {
            $this->wheres[] = [$column, '=', $comparator];
        } else {
            $this->wheres[] = [$column, $comparator, $value];
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws QueryException
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
     */
    public function getLastInsertId(): string|false
    {
        return $this->connection->pdo()->lastInsertId();
    }

    /**
     * {@inheritdoc}
     * @throws QueryException
     */
    public function delete(): int|bool
    {
        $this->type = 'delete';

        $statement = $this->prepare();

        return $statement->execute($this->getWhereValues());
    }
}
