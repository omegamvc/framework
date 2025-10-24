<?php

declare(strict_types=1);

namespace Omega\Database\Model;

use ArrayAccess;
use ArrayIterator;
use Exception;
use IteratorAggregate;
use Omega\Database\ConnectionInterface;
use Omega\Database\Query\Query;
use Omega\Database\Query\Bind;
use Omega\Database\Query\Join\InnerJoin;
use Omega\Database\Query\AbstractQuery;
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
 * @implements ArrayAccess<array-key, mixed>
 * @implements IteratorAggregate<array-key, mixed>
 */
class Model implements ArrayAccess, IteratorAggregate
{
    /** @var ConnectionInterface  */
    protected ConnectionInterface $pdo;

    /** @var string  */
    protected string $tableName;

    /** @var string  */
    protected string $primaryKey = 'id';

    /** @var array<array<array-key, mixed>> */
    protected array $columns;

    /** @var string[] Hide from showing column */
    protected array $stash = [];

    /** @var string[] Set Column cant be modified */
    protected array $resistant = [];

    /** @var array<array<array-key, mixed>> Originate data from database */
    protected array $fresh;

    /** @var Where|null  */
    protected ?Where $where = null;

    /**
     * Binder array(['key', 'val']).
     *
     * @var Bind[] Binder for PDO bind */
    protected array $binds = [];

    /** @var int  */
    protected int $limitStart = 0;

    /** @var int  */
    protected int $limitEnd = 0;

    /** @var int  */
    protected int $offset = 0;

    /** @var array<string, string> */
    protected array $sortOrder  = [];

    /**
     * Model class constructor.
     *
     * @param array<array-key, mixed> $column
     * @return void
     */
    public function __construct(
        ConnectionInterface $pdo,
        array $column,
    ) {
        $this->pdo        = $pdo;
        $this->columns    = $this->fresh = $column;
        // auto table
        $this->tableName ??= strtolower(__CLASS__);
        $this->where = new Where($this->tableName);
    }

    /**
     * Debug information, stash exclude from showing.
     */
    public function __debugInfo()
    {
        return $this->getColumns();
    }

    /**
     * @param string                         $table
     * @param array<array<array-key, mixed>> $column
     * @param ConnectionInterface            $pdo
     * @param Where                          $where
     * @param string                         $primaryKey
     * @param string[]                       $stash
     * @param string[]                       $resistant
     * @return static
     */
    public function setUp(
        string $table,
        array $column,
        ConnectionInterface $pdo,
        Where $where,
        string $primaryKey,
        array $stash,
        array $resistant,
    ): self {
        $this->tableName  = $table;
        $this->columns    = $this->fresh = $column;
        $this->pdo        = $pdo;
        $this->where      = $where;
        $this->primaryKey = $primaryKey;
        $this->stash      = $stash;
        $this->resistant  = $resistant;

        return $this;
    }

    /**
     * Getter.
     *
     * @param string $name
     * @return mixed
     * @throws Exception
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
     * Setter.
     *
     * @param string $name
     * @param mixed $value
     * @return void
     * @throws Exception
     */
    public function __set(string $name, mixed $value): void
    {
        $this->setter($name, $value);
    }

    /**
     * Check first column has key.
     *
     * @param string $name
     * @return bool
     * @throws Exception
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /**
     * Check first column contains key.
     *
     * @param string $name
     * @return bool
     * @throws Exception
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->first());
    }

    /**
     * Setter.
     *
     * @param string $key
     * @param mixed $value
     * @return static
     * @throws Exception
     */
    public function setter(string $key, mixed $value): self
    {
        $this->firstColumn($current);
        if (key_exists($key, $this->columns[$current]) && !in_array($key, $this->resistant)) {
            $this->columns[$current][$key] = $value;

            return $this;
        }

        return $this;
    }

    /**
     * Getter.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     * @throws Exception
     */
    public function getter(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->stash)) {
            throw new Exception("Cant read this column `$key`.");
        }

        return $this->first()[$key] ?? $default;
    }

    /**
     * Get value of primary key from first column/record.
     *
     * @return mixed
     * @throws Exception No records found
     */
    public function getPrimaryKey(): mixed
    {
        $first = $this->first();
        if (false === array_key_exists($this->primaryKey, $first)) {
            throw new Exception(
                'this ' . __CLASS__ . 'model doest contain correct record, please check your query.'
            );
        }

        return $first[$this->primaryKey];
    }

    /**
     * Custom where condition (overwrite where).
     *
     * @return Where
     */
    public function identifier(): Where
    {
        return $this->where = new Where($this->tableName);
    }

    /**
     * Get first column without stash.
     *
     * @param int|string|null $key ByRef key
     * @return array<array-key, mixed>
     * @throws Exception
     */
    public function first(int|string|null &$key = null): array
    {
        $columns = $this->getColumns();
        if (null === ($key = array_key_first($columns))) {
            throw new Exception('Empty columns, try to assign using read.');
        }

        return $columns[$key];
    }

    /**
     * Fetch query return as model collection.
     *
     * @return ModelCollection<array-key, static>
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

            $collection->push(new static($this->pdo, [])->setUp(
                $this->tableName,
                [$column],
                $this->pdo,
                $where,
                $this->primaryKey,
                $this->stash,
                $this->resistant
            ));
        }

        return $collection;
    }

    /**
     * Insert all column to database.
     *
     * @return bool
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
     * Read record base where condition given.
     *
     * @return bool
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
     * Update column from database.
     *
     * @return bool
     * @throws Exception
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
     * Delete record base on where condition given.
     *
     * @return bool
     */
    public function delete(): bool
    {
        $delete = Query::from($this->tableName, $this->pdo)
            ->delete();

        return $this->changing($this->execute($delete));
    }

    /**
     * Check where condition has record or not.
     *
     * @return bool
     */
    public function isExist(): bool
    {
        $query = new Select($this->tableName, [$this->primaryKey], $this->pdo);

        $query->whereRef($this->where);

        return $this->execute($query);
    }

    /**
     * Get the model relation.
     *
     * @param string|class-string $model
     * @param string|null         $ref
     * @return Model
     */
    /**public function hasOne(string $model, ?string $ref = null): Model
    {
        if (class_exists($model)) {
            / ** @var object $model * /
            $model     = new $model($this->pdo, []);
            $tableName = $model->tableName;
            $joinRef   = $ref ?? $model->primaryKey;
        } else {
            $tableName = $model;
            $joinRef  = $ref ?? $this->primaryKey;
            $model     = new static($this->pdo, []);
        }
        $result   = Query::from($this->tableName, $this->pdo)
            ->select([$tableName . '.*'])
            ->join(InnerJoin::ref($tableName, $this->primaryKey, $joinRef))
            ->whereRef($this->where)
            ->single();
        $model->columns = $model->fresh = [$result];

        return $model;
    }*/

    /**
     * Get the model relation.
     *
     * @param string|class-string $model
     * @param string|null         $ref
     * @return ModelCollection<array-key, Model>
     */
    /**public function hasMany(string $model, ?string $ref = null): ModelCollection
    {
        if (class_exists($model)) {
            / ** @var object $model * /
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
    }*/

    /**
     * Internal relation handler for hasOne / hasMany.
     */
    protected function buildRelation(string $model, ?string $ref, bool $multiple): Model|ModelCollection
    {
        if (class_exists($model)) {
            $model     = new $model($this->pdo, []);
            $tableName = $model->tableName;
            $joinRef   = $ref ?? $model->primaryKey;
        } else {
            $tableName = $model;
            $joinRef   = $ref ?? $this->primaryKey;
            $model     = new static($this->pdo, []);
        }

        $query = Query::from($this->tableName, $this->pdo)
            ->select([$tableName . '.*'])
            ->join(InnerJoin::ref($tableName, $this->primaryKey, $joinRef))
            ->whereRef($this->where);

        if ($multiple) {
            $result = $query->get();
            $model->columns = $model->fresh = $result->toArray();

            return $model->get();
        }

        $result = $query->single();
        $model->columns = $model->fresh = [$result];

        return $model;
    }

    public function hasOne(string $model, ?string $ref = null): Model
    {
        return $this->buildRelation($model, $ref, false);
    }

    public function hasMany(string $model, ?string $ref = null): ModelCollection
    {
        return $this->buildRelation($model, $ref, true);
    }

    /**
     * Check current column has modified or not.
     *
     * @param string|null $column
     * @return bool
     * @throws Exception
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
                throw new Exception(
                    sprintf(
                        "Column {%s} is not in table `{%s}`.",
                        $column,
                        $this->tableName
                    )
                );
            }

            if (false === ($this->columns[$key][$column] === $this->fresh[$key][$column])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check current column has modified or not.
     *
     * @param string|null $column
     * @return bool
     * @throws Exception
     */
    public function isDirty(?string $column = null): bool
    {
        return !$this->isClean($column);
    }

    /**
     * Get change (diff) between fresh and current column.
     *
     * @return array<array-key, mixed>
     * @throws Exception
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
     * Convert model column to array.
     *
     * @return array<array<array-key, mixed>>
     */
    public function toArray(): array
    {
        return $this->getColumns();
    }

    /**
     * Set data start for fetch all data.
     *
     * @param int $limitStart limit start
     * @param int $limitEnd   limit end
     * @return static
     */
    public function limit(int $limitStart, int $limitEnd): self
    {
        $this->limitStart($limitStart);
        $this->limitEnd($limitEnd);

        return $this;
    }

    /**
     * Set data start for fetch all data.
     *
     * @param int $value limit start default is 0
     * @return static
     */
    public function limitStart(int $value): self
    {
        $this->limitStart = max($value, 0);

        return $this;
    }

    /**
     * Set data end for fetch all data
     * zero value meaning no data show.
     *
     * @param int $value limit start default
     * @return static
     */
    public function limitEnd(int $value): self
    {
        $this->limitEnd = max($value, 0);

        return $this;
    }

    /**
     * Set offest.
     *
     * @param int $value offset
     * @return static
     */
    public function offset(int $value): self
    {
        $this->offset = max($value, 0);

        return $this;
    }

    /**
     * Set limit using limit and offset.
     *
     * @param int $limit
     * @param int $offset
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
     * Set sort column and order
     * column name must register.
     *
     * @param string      $columnName
     * @param int         $orderUsing
     * @param string|null $belongTo
     * @return $this
     */
    public function order(string $columnName, int $orderUsing = Query::ORDER_ASC, ?string $belongTo = null): self
    {
        $order = 0 === $orderUsing ? 'ASC' : 'DESC';
        $belongTo ??= $this->tableName;
        $res = $belongTo . $columnName;

        $this->sortOrder[$res] = $order;

        return $this;
    }

    /**
     * @param array-key $offset
     * @throws Exception
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @param array-key $offset
     * @return mixed|null
     * @throws Exception
     */
    #[ReturnTypeWillChange]
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getter($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @throws Exception
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setter($offset, $value);
    }

    public function offsetUnset($offset): void
    {
    }

    /**
     * Get iterator.
     *
     * @return Traversable<array-key, mixed>
     * @throws Exception
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->first());
    }

    /**
     * Find model using defined primary key.
     *
     * @param int|string          $id
     * @param ConnectionInterface $pdo
     * @return Model
     */
    public static function find(int|string $id, ConnectionInterface $pdo): static
    {
        $model          = new static($pdo, []);
        $model->where   = new Where($model->tableName)
            ->equal($model->primaryKey, $id);

        $model->read();

        return $model;
    }

    /**
     * Find model using defined primary key.
     *
     * @param mixed                   $id
     * @param array<array-key, mixed> $column
     * @param ConnectionInterface     $pdo
     * @return static
     * @throws Exception cant inset data
     */
    public static function findOrCreate(mixed $id, array $column, ConnectionInterface $pdo): static
    {
        $model          = new static($pdo, [$column]);
        $model->where   = new Where($model->tableName)
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
     * Find model using custom where.
     *
     * @param string              $whereCondition
     * @param array<string|int>   $binder
     * @param ConnectionInterface $pdo
     * @return static
     */
    public static function where(string $whereCondition, array $binder, ConnectionInterface $pdo): static
    {
        $model = new static($pdo, []);
        $map   = [];
        foreach ($binder as $bind => $value) {
            $map[] = [$bind, $value];
        }

        $model->where = new Where($model->tableName)
            ->where($whereCondition, $map);
        $model->read();

        return $model;
    }

    /**
     * Find model using custom equal.
     *
     * @param array-key $columnName
     * @param mixed $value
     * @param ConnectionInterface $pdo
     * @return static
     */
    public static function equal(int|string $columnName, mixed $value, ConnectionInterface $pdo): static
    {
        $model = new static($pdo, []);

        $model->identifier()->equal($columnName, $value);
        $model->read();

        return $model;
    }

    /**
     * Fetch all records.
     *
     * @param ConnectionInterface $pdo
     * @return ModelCollection<array-key, static>
     */
    public static function all(ConnectionInterface $pdo): ModelCollection
    {
        $model = new static($pdo, []);
        $model->read();

        return $model->get();
    }

    /**
     * Get current column without stash.
     *
     * @return array<array<array-key, mixed>>
     */
    protected function getColumns(): array
    {
        return array_map(
            fn ($column) => array_filter(
                $column,
                fn ($k) => !in_array($k, $this->stash, true),
                ARRAY_FILTER_USE_KEY
            ),
            $this->columns
        );
    }

    /**
     * Get first column.
     *
     * @param int|string|null $key ByRef key
     * @return array<array-key, mixed>
     * @throws Exception
     */
    protected function firstColumn(int|string|null &$key = null): array
    {
        if (null === ($key = array_key_first($this->columns))) {
            throw new Exception('Empty columns, try to assign using read.');
        }

        return $this->columns[$key];
    }

    /**
     * Reverse fresh column with current column.
     *
     * @param bool $change
     * @return bool
     */
    private function changing(bool $change): bool
    {
        if ($change) {
            $this->fresh = $this->columns;
        }

        return $change;
    }

    /**
     * Get binder.
     *
     * @param AbstractQuery $query
     * @return array<Bind[]|string>
     *
     * @return array
     */
    private function builder(AbstractQuery $query): array
    {
        return [
            (fn () => $this->{'builder'}())->call($query),
            (fn () => $this->{'_binds'})->call($query),
        ];
    }

    /**
     * Fetch pdo query result.
     *
     * @param AbstractQuery $baseQuery
     * @return array|false
     */
    private function fetch(AbstractQuery $baseQuery): array|false
    {
        // custom where
        $baseQuery->whereRef($this->where);

        [$query, $binds] = $this->builder($baseQuery);

        $this->pdo->query($query);
        foreach ($binds as $bind) {
            if (!$bind->hasBind()) {
                $this->pdo->bind($bind->getBind(), $bind->getValue());
            }
        }

        return $this->pdo->resultset();
    }

    /**
     * Execute query with where condition given.
     *
     * @param AbstractQuery $baseQuery
     * @return bool
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
