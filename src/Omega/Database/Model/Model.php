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

namespace Omega\Database\Model;

use ArrayAccess;
use ArrayIterator;
use Exception;
use IteratorAggregate;
use Omega\Database\Connection;
use Omega\Database\Query\AbstractQuery;
use Omega\Database\Query\Bind;
use Omega\Database\Query\Join\InnerJoin;
use Omega\Database\Query\Query;
use Omega\Database\Query\Select;
use Omega\Database\Query\Where;
use ReturnTypeWillChange;
use Traversable;

use function array_filter;
use function array_key_exists;
use function array_key_first;
use function array_keys;
use function class_exists;
use function in_array;
use function is_a;
use function key_exists;
use function max;
use function method_exists;
use function sprintf;

use const ARRAY_FILTER_USE_KEY;

/**
 * Represents a base ORM (Object-Relational Mapping) model.
 *
 * This class provides an abstraction layer for interacting with a database table.
 * It supports dynamic data manipulation, CRUD operations, relationships (hasOne, hasMany),
 * change tracking (clean/dirty), query filtering, pagination, and sorting.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Model
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @implements ArrayAccess<array-key, mixed>
 * @implements IteratorAggregate<array-key, mixed>
 */
class Model implements ArrayAccess, IteratorAggregate
{
    /** @var Connection The PDO connection instance used to interact with the database. */
    protected Connection $pdo;

    /** @var string The name of the database table associated with this model. */
    protected string $tableName;

    /** @var string The primary key column name. Defaults to 'id'. */
    protected string $primaryKey = 'id';

    /** @var array<array<array-key, mixed>> The current dataset loaded into the model. */
    protected array $columns;

    /** @var string[] List of columns to be excluded when accessing or displaying the model data. */
    protected array $stash = [];

    /** @var string[] List of columns that cannot be modified. */
    protected array $immutableColumn = [];

    /** @var array<array<array-key, mixed>> Original data loaded from the database. */
    protected array $fresh;

    /** @var Where|null WHERE clause used to filter queries. Can be overwritten with identifier(). */
    protected ?Where $where = null;

    /** @var Bind[] List of bound values used for PDO prepared statements. Each Bind contains the key and value */
    protected array $binds = [];

    /** @var int Starting point for query limit. */
    protected int $limitStart = 0;

    /** @var int Maximum number of records to return. A value of 0 means no limit. */
    protected int $limitEnd = 0;

    /** @var int Offset to apply in SELECT queries. */
    protected int $offset = 0;

    /** @var array<string, string> Sort order in SELECT. The key is "table.column" and the value is "ASC" or "DESC". */
    protected array $sortOrder = [];

    /**
     * Model constructor.
     *
     * Initializes the model with a PDO connection and an array of column data.
     * Also sets the table name (defaulting to the class name in lowercase) and initializes the WHERE clause.
     *
     * @param Connection               $pdo    PDO connection instance.
     * @param array<array-key, mixed> $column Initial dataset for the model.
     * @return void
     */
    public function __construct(Connection $pdo, array $column)
    {
        $this->pdo        = $pdo;
        $this->columns    = $this->fresh = $column;
        // auto table
        $this->tableName ??= strtolower(__CLASS__);
        $this->where = new Where($this->tableName);
    }

    /**
     * Returns debug information for the model.
     * Excludes any columns listed in the stash.
     *
     * @return array<array<array-key, mixed>> Filtered column data.
     */
    public function __debugInfo()
    {
        return $this->getColumns();
    }

    /**
     * Manually sets up the model instance with table and configuration details.
     *
     * @param string                         $table           Table name.
     * @param array<array<array-key, mixed>> $column          Model column data.
     * @param Connection                     $pdo             PDO connection instance.
     * @param Where                          $where           Custom WHERE clause.
     * @param string                         $primaryKey      Primary key column name.
     * @param string[]                       $stash           Columns to hide from output.
     * @param string[]                       $immutableColumn Columns that cannot be modified.
     * @return self Returns the configured model instance.
     */
    public function setUp(
        string $table,
        array $column,
        Connection $pdo,
        Where $where,
        string $primaryKey,
        array $stash,
        array $immutableColumn,
    ): self {
        $this->tableName       = $table;
        $this->columns         = $this->fresh = $column;
        $this->pdo             = $pdo;
        $this->where           = $where;
        $this->primaryKey      = $primaryKey;
        $this->stash           = $stash;
        $this->immutableColumn = $immutableColumn;

        return $this;
    }

    /**
     * Magic getter for model properties and dynamic relationships.
     *
     * If a method with the given name exists, it will be called and interpreted
     * as a relationship (hasOne/hasMany). Otherwise, it fetches the column value.
     *
     * @param string $name Property or method name.
     * @return mixed
     * @throws Exception If property is not accessible or not found.
     */
    public function __get(string $name)
    {
        if (method_exists($this, $name)) {
            $highOrder = $this->{$name}();
            if (is_a($highOrder, Model::class)) {
                return $highOrder->first();
            }

            if (is_a($highOrder, ModelCollection::class)) {
                return $highOrder->toArrayArray();
            }
        }

        return $this->getter($name);
    }

    /**
     * Magic setter for assigning column values dynamically.
     *
     * @param string $name  Column name.
     * @param mixed  $value Value to assign.
     * @return void
     * @throws Exception If assignment fails.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->setter($name, $value);
    }

    /**
     * Checks whether the given column exists in the first record.
     *
     * @param string $name Column name.
     * @return bool
     * @throws Exception If no data is present.
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * Checks if the specified column exists in the first data row.
     *
     * @param string $name Column name.
     * @return bool
     * @throws Exception If no data is present.
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->first());
    }

    /**
     * Sets a value on the first available column row.
     *
     * The column must exist and not be listed in the immutable columns.
     *
     * @param string $key   Column name.
     * @param mixed  $value Value to assign.
     * @return static
     * @throws Exception If no data is present or the column is immutable.
     */
    public function setter(string $key, mixed $value): self
    {
        $this->firstColumn($current);
        if (key_exists($key, $this->columns[$current]) && !in_array($key, $this->immutableColumn)) {
            $this->columns[$current][$key] = $value;

            return $this;
        }

        return $this;
    }

    /**
     * Retrieves the value of a column from the first data row.
     *
     * Returns the default value if the column is not set,
     * or throws an exception if the column is in the stash.
     *
     * @param string     $key     Column name.
     * @param mixed|null $default Default value if not found.
     * @return mixed
     * @throws Exception If the column is hidden in the stash.
     */
    public function getter(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->stash)) {
            throw new Exception(sprintf("Cant read this column %s ", $key));
        }

        return $this->first()[$key] ?? $default;
    }

    /**
     * Retrieves the value of the primary key from the first row.
     *
     * @return mixed
     * @throws Exception If the primary key is not found in the row.
     */
    public function getPrimaryKey(): mixed
    {
        $first = $this->first();
        if (false === array_key_exists($this->primaryKey, $first)) {
            throw new Exception('this ' . __CLASS__ . 'model doest contain correct record, please check your query.');
        }

        return $first[$this->primaryKey];
    }

    /**
     * Resets the WHERE condition to a new instance.
     *
     * @return Where New Where clause instance.
     */
    public function identifier(): Where
    {
        return $this->where = new Where($this->tableName);
    }

    /**
     * Returns the first column after filtering out hidden (stash) keys.
     *
     * @param int|string|null $key Reference to the returned key index.
     * @return array<array-key, mixed> The first available column.
     * @throws Exception If no data is available.
     */
    public function first(int|string &$key = null): array
    {
        $columns = $this->getColumns();
        if (null === ($key = array_key_first($columns))) {
            throw new Exception('Empty columns, try to assign using read.');
        }

        return $columns[$key];
    }

    /**
     * Converts all current rows into a collection of model instances.
     *
     * @return ModelCollection<array-key, static> A collection of model objects.
     */
    public function get(): ModelCollection
    {
        /** @var ModelCollection<array-key, static> $collection */
        $collection = new ModelCollection([], $this);
        foreach ($this->columns as $column) {
            $where = new Where($this->tableName);
            if (array_key_exists($this->primaryKey, $column)) {
                $where->equal($this->primaryKey, $column[$this->primaryKey]);
            }

            $collection->push((new static($this->pdo, []))->setUp(
                $this->tableName,
                [$column],
                $this->pdo,
                $where,
                $this->primaryKey,
                $this->stash,
                $this->immutableColumn
            ));
        }

        return $collection;
    }

    /**
     * Inserts all records into the database.
     *
     * @return bool True if all inserts succeed, false otherwise.
     */
    public function insert(): bool
    {
        $insert = Query::from($this->tableName, $this->pdo);
        foreach ($this->columns as $column) {
            $success = $insert->insert()
                ->values($column)
                ->execute();

            if (!$success) {
                return false;
            }
        }

        return true;
    }

    /**
     * Reads records from the database using the current WHERE condition.
     *
     * @return bool True if any rows are found, false otherwise.
     */
    public function read(): bool
    {
        $query = new Select($this->tableName, ['*'], $this->pdo);

        $query->sortOrderRef($this->limitStart, $this->limitEnd, $this->offset, $this->sortOrder);

        $all = $this->fetch($query);

        if ([] === $all) {
            return false;
        }

        $this->columns = $this->fresh = $all;

        return true;
    }

    /**
     * Updates the current record in the database.
     *
     * @return bool True if any rows were affected, false otherwise.
     * @throws Exception If change detection fails.
     */
    public function update(): bool
    {
        if ($this->isClean()) {
            return false;
        }

        $update = Query::from($this->tableName, $this->pdo)
            ->update()
            ->values(
                $this->changes()
            );

        return $this->changing($this->execute($update));
    }

    /**
     * Deletes records from the database using the current WHERE condition.
     *
     * @return bool True if deletion succeeds, false otherwise.
     */
    public function delete(): bool
    {
        $delete = Query::from($this->tableName, $this->pdo)
            ->delete();

        return $this->changing($this->execute($delete));
    }

    /**
     * Checks if any record exists matching the current WHERE condition.
     *
     * @return bool True if record exists, false otherwise.
     */
    public function isExist(): bool
    {
        $query = new Select($this->tableName, [$this->primaryKey], $this->pdo);

        $query->whereRef($this->where);

        return $this->execute($query);
    }

    /**
     * Defines a one-to-one relationship with another model.
     *
     * @param string|class-string $model Related model class name or table name.
     * @param string|null         $ref   Foreign key reference.
     * @return Model Related model instance.
     */
    public function hasOne(string $model, ?string $ref = null): self
    {
        /** @noinspection DuplicatedCode */
        if (class_exists($model)) {
            /** @var object $model */
            $model     = new $model($this->pdo, []);
            $tableName = $model->tableName;
            $joinRef   = $ref ?? $model->primaryKey;
        } else {
            $tableName = $model;
            $joinRef   = $ref ?? $this->primaryKey;
            $model     = new static($this->pdo, []);
        }
        $result   = Query::from($this->tableName, $this->pdo)
            ->select([$tableName . '.*'])
            ->join(InnerJoin::ref($tableName, $this->primaryKey, $joinRef))
            ->whereRef($this->where)
            ->single();
        $model->columns = $model->fresh = [$result];

        return $model;
    }

    /**
     * Defines a one-to-many relationship with another model.
     *
     * @param string|class-string $model Related model class name or table name.
     * @param string|null         $ref   Foreign key reference.
     * @return ModelCollection<array-key, Model> Collection of related models.
     */
    public function hasMany(string $model, ?string $ref = null): ModelCollection
    {
        /** @noinspection DuplicatedCode */
        if (class_exists($model)) {
            /** @var object $model */
            $model     = new $model($this->pdo, []);
            $tableName = $model->tableName;
            $joinRef   = $ref ?? $model->primaryKey;
        } else {
            $tableName = $model;
            $joinRef   = $ref ?? $this->primaryKey;
            $model     = new static($this->pdo, []);
        }
        $result = Query::from($this->tableName, $this->pdo)
             ->select([$tableName . '.*'])
             ->join(InnerJoin::ref($tableName, $this->primaryKey, $joinRef))
             ->whereRef($this->where)
             ->get();
        $model->columns = $model->fresh = $result->toArray();

        return $model->get();
    }

    /**
     * Checks if the current column or entire record is unchanged.
     *
     * @param string|null $column Optional column name to check.
     * @return bool True if clean, false if modified.
     * @throws Exception If the column is not found.
     */
    public function isClean(?string $column = null): bool
    {
        if ($column === null) {
            return $this->columns === $this->fresh;
        }

        if (false === (array_keys($this->columns) === array_keys($this->fresh))) {
            return false;
        }

        foreach (array_keys($this->columns) as $key) {
            if (
                !array_key_exists($column, $this->columns[$key])
                || !array_key_exists($column, $this->fresh[$key])
            ) {
                throw new Exception(sprintf(
                    'Column %s is not in table `%s`.',
                    $column,
                    $this->tableName
                ));
            }

            if (false === ($this->columns[$key][$column] === $this->fresh[$key][$column])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the current column or entire record has been modified.
     *
     * @param string|null $column Optional column name to check.
     * @return bool True if modified, false otherwise.
     * @throws Exception If the column is not found.
     */
    public function isDirty(?string $column = null): bool
    {
        return !$this->isClean($column);
    }

    /**
     * Returns the difference between current and original (fresh) data.
     *
     * @return array<array-key, mixed> Modified key-value pairs.
     * @throws Exception If column lookup fails.
     */
    public function changes(): array
    {
        $change = [];

        $column = $this->firstColumn($current);
        if (false === array_key_exists($current, $this->fresh)) {
            return $column;
        }

        foreach ($column as $key => $value) {
            if (
                array_key_exists($key, $this->fresh[$current])
                && $this->fresh[$current][$key] !== $value
            ) {
                $change[$key] = $value;
            }
        }

        return $change;
    }

    /**
     * Converts the model to an array of rows.
     *
     * @return array<array<array-key, mixed>> Array of column data.
     */
    public function toArray(): array
    {
        return $this->getColumns();
    }

    /**
     * Sets both the starting and ending limits for result pagination.
     *
     * @param int $limitStart Start index.
     * @param int $limitEnd   End index.
     * @return self
     */
    public function limit(int $limitStart, int $limitEnd): self
    {
        $this->limitStart($limitStart);
        $this->limitEnd($limitEnd);

        return $this;
    }

    /**
     * Sets the starting index for result pagination.
     *
     * @param int $value Starting limit (defaults to 0).
     * @return static
     */
    public function limitStart(int $value): self
    {
        $this->limitStart = max($value, 0);

        return $this;
    }

    /**
     * Sets the ending index for result pagination.
     * A value of 0 means no results are shown.
     *
     * @param int $value Ending limit (defaults to 0).
     * @return static
     */
    public function limitEnd(int $value): self
    {
        $this->limitEnd = max($value, 0);

        return $this;
    }

    /**
     * Sets the offset for paginated results.
     *
     * @param int $value Offset value.
     * @return static
     */
    public function offset(int $value): self
    {
        $this->offset = max($value, 0);

        return $this;
    }

    /**
     * Applies limit and offset together for paginated results.
     *
     * @param int $limit  Maximum number of rows.
     * @param int $offset Number of rows to skip.
     * @return static
     */
    public function limitOffset(int $limit, int $offset): self
    {
        return $this
            ->limitStart($limit)
            ->limitEnd(0)
            ->offset($offset);
    }

    /**
     * Sets sorting for a given column.
     * Column name must exist in the table schema.
     *
     * @param string      $columnName Column to sort.
     * @param int         $orderUsing Sort order (Query::ORDER_ASC or Query::ORDER_DESC).
     * @param string|null $belongTo   Table alias or name (defaults to model's table).
     * @return $this
     */
    public function order(string $columnName, int $orderUsing = Query::ORDER_ASC, ?string $belongTo = null): self
    {
        $order = 0 === $orderUsing ? 'ASC' : 'DESC';
        $belongTo ??= $this->tableName;
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        $res = "{$belongTo}.{$columnName}";

        $this->sortOrder[$res] = $order;

        return $this;
    }

    /**
     * Checks if the given offset exists.
     *
     * @param array-key $offset
     * @return bool
     * @throws Exception If the column is not accessible.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Retrieves a value at the given offset.
     *
     * @param array-key $offset
     * @return mixed|null Value at the given offset, or null if not found.
     * @throws Exception If the column is inaccessible or stashed.
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getter($offset);
    }

    /**
     * Sets a value at the given offset.
     *
     * @param mixed $offset Offset key.
     * @param mixed $value  Value to set.
     * @return void
     * @throws Exception If setting is not allowed or the column is immutable.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setter($offset, $value);
    }

    /**
     * Unsets the value at the given offset.
     *
     * @param mixed $offset Offset key.
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
    }

    /**
     * Returns an iterator for the current record.
     *
     * @return Traversable<array-key, mixed>
     * @throws Exception If no data is available.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->first());
    }

    /**
     * Finds a model by primary key.
     *
     * @param int|string $id  Primary key value.
     * @param Connection $pdo Database connection.
     * @return static
     */
    public static function find(int|string $id, Connection $pdo): static
    {
        $model          = new static($pdo, []);
        $model->where   = (new Where($model->tableName))
            ->equal($model->primaryKey, $id);

        $model->read();

        return $model;
    }

    /**
     * Finds a model by primary key or creates it with the provided data.
     *
     * @param mixed                   $id     Primary key value.
     * @param array<array-key, mixed> $column Data to insert if not found.
     * @param Connection              $pdo    Database connection.
     * @return static
     * @throws Exception If insert fails.
     */
    public static function findOrCreate(mixed $id, array $column, Connection $pdo): static
    {
        $model          = new static($pdo, [$column]);
        $model->where   = (new Where($model->tableName))
            ->equal($model->primaryKey, $id);

        if ($model->isExist()) {
            $model->read();

            return $model;
        }

        if ($model->insert()) {
            return $model;
        }

        throw new Exception('Cant inset data.');
    }

    /**
     * Finds a model using a custom where clause.
     *
     * @param string            $whereCondition SQL where condition.
     * @param array<string|int> $binder         Bind values for the condition.
     * @param Connection        $pdo            Database connection.
     * @return static
     */
    public static function where(string $whereCondition, array $binder, Connection $pdo): static
    {
        $model = new static($pdo, []);
        $map   = [];
        foreach ($binder as $bind => $value) {
            $map[] = [$bind, $value];
        }

        $model->where = (new Where($model->tableName))
            ->where($whereCondition, $map);
        $model->read();

        return $model;
    }

    /**
     * Finds a model using an equality condition.
     *
     * @param array-key  $columnName Column name.
     * @param mixed      $value      Value to match.
     * @param Connection $pdo        Database connection.
     * @return static
     */
    public static function equal(int|string $columnName, mixed $value, Connection $pdo): static
    {
        $model = new static($pdo, []);

        $model->identifier()->equal($columnName, $value);
        $model->read();

        return $model;
    }

    /**
     * Retrieves all rows for the model.
     *
     * @param Connection $pdo Database connection.
     * @return ModelCollection<array-key, static>
     */
    public static function all(Connection $pdo): ModelCollection
    {
        $model = new static($pdo, []);
        $model->read();

        return $model->get();
    }

    /**
     * Retrieves all columns excluding stashed fields.
     *
     * @return array<array<array-key, mixed>> Filtered columns.
     */
    protected function getColumns(): array
    {
        $columns = [];
        /** @noinspection PhpLoopCanBeConvertedToArrayMapInspection */
        foreach ($this->columns as $key => $column) {
            $columns[$key] = array_filter($column, fn ($k) => false === in_array($k, $this->stash), ARRAY_FILTER_USE_KEY);
        }

        return $columns;
    }

    /**
     * Retrieves the first column and optionally its key.
     *
     * @param int|string|null $key Reference variable to receive the key.
     * @return array<array-key, mixed> The first column's data.
     * @throws Exception If there are no columns.
     */
    protected function firstColumn(int|string &$key = null): array
    {
        if (null === ($key = array_key_first($this->columns))) {
            throw new Exception('Empty columns, try to assign using read.');
        }

        return $this->columns[$key];
    }

    /**
     * Synchronizes current state as fresh if a change occurred.
     *
     * @param bool $change Whether the operation resulted in a change.
     * @return bool The same value as input.
     */
    private function changing(bool $change): bool
    {
        if ($change) {
            $this->fresh = $this->columns;
        }

        return $change;
    }

    /**
     * Builds the SQL query and its bound parameters.
     *
     * @param AbstractQuery $query The query to compile.
     * @return array{0: Bind[], 1: string} Query and its bindings.
     */
    private function builder(AbstractQuery $query): array
    {
        return [
            (fn () => $this->{'builder'}())->call($query),
            (fn () => $this->{'binds'})->call($query),
        ];
    }

    /**
     * Executes a SELECT query and returns the result set.
     *
     * @param AbstractQuery $baseQuery Query instance to execute.
     * @return array<array-key, mixed>|false The result set, or false on failure.
     */
    private function fetch(AbstractQuery $baseQuery): array|false
    {
        // costume where
        $baseQuery->whereRef($this->where);

        [$query, $binds] = $this->builder($baseQuery);

        $this->pdo->query($query);
        foreach ($binds as $bind) {
            if (!$bind->hasBind()) {
                $this->pdo->bind($bind->getBind(), $bind->getValue());
            }
        }

        return $this->pdo->resultSet();
    }

    /**
     * Executes a non-SELECT query (INSERT/UPDATE/DELETE).
     *
     * @param AbstractQuery $baseQuery Query instance to execute.
     * @return bool True if at least one row was affected.
     */
    private function execute(AbstractQuery $baseQuery): bool
    {
        $baseQuery->whereRef($this->where);

        [$query, $binds] = $this->builder($baseQuery);

        if ($query != null) {
            $this->pdo->query($query);
            foreach ($binds as $bind) {
                if (!$bind->hasBind()) {
                    $this->pdo->bind($bind->getBind(), $bind->getValue());
                }
            }

            $this->pdo->execute();

            return $this->pdo->rowCount() > 0;
        }

        return false;
    }
}
