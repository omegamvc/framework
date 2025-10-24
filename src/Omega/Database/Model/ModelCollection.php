<?php

declare(strict_types=1);

namespace Omega\Database\Model;

use Exception;
use Omega\Collection\Collection;
use Omega\Database\Query\Delete;
use Omega\Database\Query\Update;

use function array_merge;

/**
 * @extends Collection<array-key, Model>
 */
class ModelCollection extends Collection
{
    /** @var Model */
    private Model $model;

    /**
     * @param iterable<array-key, Model> $models
     * @param Model $of
     */
    public function __construct(iterable $models, Model $of)
    {
        parent::__construct($models);

        $this->model = $of;
    }

    /**
     * Get value of primary key from first column/record.
     *
     * @return array
     * @throws Exception No records found
     */
    public function getPrimaryKey(): array
    {
        $primaryKeys = [];
        foreach ($this->collection as $model) {
            $primaryKeys[] = $model->getPrimaryKey();
        }

        return $primaryKeys;
    }

    /**
     *  Check every Model has clean column.
     *
     * @param string|null $column
     * @return bool
     */
    public function isClean(?string $column = null): bool
    {
        return $this->every(fn ($model) => $model->isClean($column));
    }

    /**
     * Check every Model has dirty column.
     *
     * @param string|null $column
     * @return bool
     */
    public function isDirty(?string $column = null): bool
    {
        return !$this->isClean($column);
    }

    /**
     * Update using query using model primary key.
     *
     * @param array<array-key, mixed> $values
     * @return bool
     * @throws Exception
     */
    public function update(array $values): bool
    {
        $tableName  = (fn () => $this->{'table_name'})->call($this->model);
        $pdo        = (fn () => $this->{'pdo'})->call($this->model);
        $primaryKey = (fn () => $this->{'primary_key'})->call($this->model);
        $update     = new Update($tableName, $pdo);

        $update->values($values)->in($primaryKey, $this->getPrimaryKey());

        return $update->execute();
    }

    /**
     * Delete using query using model primary key.
     *
     * @return bool
     * @throws Exception
     */
    public function delete(): bool
    {
        $tableName  = (fn () => $this->{'table_name'})->call($this->model);
        $pdo        = (fn () => $this->{'pdo'})->call($this->model);
        $primaryKey = (fn () => $this->{'primary_key'})->call($this->model);
        $delete     = new Delete($tableName, $pdo);

        $delete->in($primaryKey, $this->getPrimaryKey());

        return $delete->execute();
    }

    /**
     * Convert array of model to pure array;.
     *
     * @return array<array-key, mixed>
     */
    public function toArrayArray(): array
    {
        /** @var array<array-key, mixed> $arr */
        $arr = [];
        foreach ($this->collection as $model) {
            $arr = array_merge($arr, $model->toArray());
        }

        return $arr;
    }
}
