<?php

/** @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection */

/**
 * Part of Omega -  Model Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database;

use Exception;
use ReflectionClass;
use Omega\Database\Adapter\DatabaseAdapterInterface;
use Omega\Database\Exception\UndefinedTableNameException;

/**
 * Abstract model class.
 *
 * The `AbstractModel` class providing the base for model class.
 *
 * @category   Omega
 * @package    Database
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
abstract class AbstractModel
{
    /**
     * Current database instance.
     *
     * @var DatabaseAdapterInterface Holds the current database instance.
     */
    protected DatabaseAdapterInterface $connection;

    /**
     * Table name in the database.
     *
     * @var string Holds the table name in the database.
     */
    protected string $table;

    /**
     * Model attributes.
     *
     * @var array<string, mixed> Holds the model attributes.
     */
    protected array $attributes = [];

    /**
     * Modified attributes,.
     *
     * @var array<string> Holds the modified attributes.
     */
    protected array $dirty = [];

    /**
     * Attribute casting types.
     *
     * @var array<string, callable(mixed): mixed> Holds an array of attributes casting types.
     */
    protected array $casts = [];

    /**
     * Set the model's database connection.
     *
     * @param DatabaseAdapterInterface $connection Holds the database connection.
     * @return static
     */
    public function setConnection(DatabaseAdapterInterface $connection): static
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Get the model's database connection.
     *
     * @return DatabaseAdapterInterface Return the current instance of DatabaseAdapterInterface.
     */
    public function getConnection(): DatabaseAdapterInterface
    {
        if (!isset($this->connection)) {
            $this->connection = app('database');
            assert($this->connection instanceof DatabaseAdapterInterface);
        }

        return $this->connection;
    }

    /**
     * Set the table name in the database.
     *
     * @param string $table Holds the table name to set.
     * @return static
     */
    public function setTable(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get the table.
     *
     * @return string Return the table name.
     * @throws UndefinedTableNameException if the table is not set or getTable is not defined.
     */
    public function getTable(): string
    {
        if (! isset($this->table)) {
            $reflector = new ReflectionClass(static::class);

            foreach ($reflector->getAttributes() as $attribute) {
                if ($attribute->getName() == TableName::class) {
                    return $attribute->getArguments()[0];
                }
            }

            throw new UndefinedTableNameException(
                '$table is not set and getTable is not defined'
            );
        }

        return $this->table;
    }

    /**
     * Model with attributes,.
     *
     * @param array<string, mixed> $attributes Holds an array of attributes.
     * @return static
     */
    public static function with(array $attributes = []): static
    {
        $model             = new static();
        $model->attributes = $attributes;

        return $model;
    }

    /**
     * Execute the query.
     *
     * @return mixed
     * @throws Exception
     */
    public static function query(): mixed
    {
        $model = new static();
        $query = $model->getConnection()->query();

        return ( new ModelCollector($query, static::class) )
            ->from($model->getTable());
    }

    /**
     *  Magic method to forward undefined method calls to the underlying query builder instance.
     *
     *  This method allows dynamic method calls on the ModelCollector, delegating them to the
     *  underlying query builder instance. This is particularly useful for building queries fluently.
     *
     * @param string       $method     Holds the method name.
     * @param array<mixed> $parameters Holds the method parameters.
     * @return $this|mixed Return $this if the method is fluent, otherwise, returns the method result.
     * @throws Exception if the table is not set or getTable is not defined.
     */
    public static function __callStatic(string $method, array $parameters = []): mixed
    {
        return static::query()->$method(...$parameters);
    }

    /**
     * Magic get.
     *
     * @param string $property Holds the property name.
     * @return mixed
     */
    public function __get(string $property): mixed
    {
        $getter = 'get' . ucfirst($property) . 'Attribute';
        $value  = null;

        if (method_exists($this, $property)) {
            $relationship = $this->$property();
            $method       = $relationship->method;

            $value = $relationship->$method();
        }

        if (method_exists($this, $getter)) {
            $value = $this->$getter($this->attributes[$property] ?? null);
        }

        if (isset($this->attributes[$property])) {
            $value = $this->attributes[$property];
        }

        if (isset($this->casts[$property]) && is_callable($this->casts[$property])) {
            $value = $this->casts[$property]($value);
        }

        return $value;
    }

    /**
     * Set the property.
     *
     * @param string $property Holds the property name.
     * @param mixed  $value    Holds the property value.
     * @return void
     */
    public function __set(string $property, mixed $value): void
    {
        $setter = 'set' . ucfirst($property) . 'Attribute';

        $this->dirty[] = $property;

        if (method_exists($this, $setter)) {
            $this->attributes[$property] = $this->$setter($value);

            return;
        }

        $this->attributes[$property] = $value;
    }

    /**
     * Save the model's changes to the database.
     *
     * @return static
     * @throws Exception
     * @throws UndefinedTableNameException if the table is not set or getTable is not defined.
     */
    public function save(): static
    {
        $values = [];

        foreach ($this->dirty as $dirty) {
            $values[$dirty] = $this->attributes[$dirty];
        }

        $data  = [ array_keys($values), $values ];
        $query = static::query();

        if (isset($this->attributes['id'])) {
            $query
                ->where('id', $this->attributes['id'])
                ->update(...$data);

            return $this;
        }

        $query->insert(...$data);

        $this->attributes['id'] = $query->getLastInsertId();
        $this->dirty            = [];

        return $this;
    }

    /**
     * Delete the model from the database.
     *
     * @return static
     * @throws Exception
     * @throws UndefinedTableNameException if the table is not set or getTable is not defined.
     */
    public function delete(): static
    {
        if (isset($this->attributes['id'])) {
            static::query()
                ->where('id', $this->attributes['id'])
                ->delete();
        }

        return $this;
    }

    /**
     * Define a "hasOne" relationship between models.
     *
     * @param string $class      Holds the name of the related model class.
     * @param string $foreignKey Holds the foreign key in the current model.
     * @param string $primaryKey Holds the primary key in the related model.
     * @return Relationship Return the current instance of relation.
     */
    public function hasOne(string $class, string $foreignKey, string $primaryKey = 'id'): Relationship
    {
        $model = new $class();
        $query = $class::query()->from($model->getTable())->where($foreignKey, $this->attributes['id']);

        return new Relationship($query, 'first');
    }

    /**
     * Define a "hasNay" relationship between models.
     *
     * @param string $class      Holds the name of the related model class.
     * @param string $foreignKey Holds the foreign key in the current model.
     * @param string $primaryKey Holds the primary key in the related model.
     * @return Relationship Return the current instance of relation.
     */
    public function hasMany(string $class, string $foreignKey, string $primaryKey = 'id'): Relationship
    {
        $model = new $class();
        $query = $class::query()->from($model->getTable())->where($foreignKey, $this->attributes['id']);

        return new Relationship($query, 'all');
    }

    /**
     * Define a "belongsTo" relationship between models.
     *
     * @param string $class      Holds the name of the related model class.
     * @param string $foreignKey Holds the foreign key in the current model.
     * @param string $primaryKey Holds the primary key in the related model.
     * @return Relationship Return the current instance of relation.
     */
    public function belongsTo(string $class, string $foreignKey, string $primaryKey = 'id'): Relationship
    {
        $model = new $class();
        $query = $class::query()->from($model->getTable())->where($primaryKey, $this->attributes[$foreignKey]);

        return new Relationship($query, 'first');
    }

    /**
     * Find a model by ID.
     *
     * @param int $id Holds the ID of the model to find.
     * @return static
     */
    public static function find(int $id): static
    {
        return static::where('id', $id)->first();
    }
}
