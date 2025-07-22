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

use Exception;
use Omega\Collection\Collection;
use Omega\Database\Query\Delete;
use Omega\Database\Query\Update;

use function array_merge;

/**
 * Class ModelCollection
 *
 * A collection wrapper specifically for `Model` instances.
 * Provides additional functionality tailored to ORM operations,
 * such as checking clean/dirty states, batch updates and deletes,
 * and extraction of primary keys.
 *
 * Inherits from the generic Collection class and assumes all items
 * are instances of the same `Model` subclass.
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
 * @extends Collection<array-key, Model>
 */
class ModelCollection extends Collection
{
    /** @var Model A reference model used to retrieve metadata like table name and primary key. */
    private Model $model;

    /**
     * Create a collection of Model instances with reference to a single model metadata provider.
     *
     * @param iterable<array-key, Model> $models A list of model instances.
     * @param Model                      $of     The model used to access internal metadata.
     * @return void
     */
    public function __construct(iterable $models, Model $of)
    {
        parent::__construct($models);

        $this->model = $of;
    }

    /**
     * Retrieve the primary key(s) from each model in the collection.
     *
     * @return array An array of primary key values.
     * @throws Exception If no records exist or keys cannot be determined.
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
     * Determine if all models are "clean" (i.e., have no changed fields),
     * or a specific column is unchanged across all models.
     *
     * @param string|null $column Optional column name to check.
     * @return bool True if all models are clean.
     */
    public function isClean(?string $column = null): bool
    {
        return $this->every(fn ($model) => $model->isClean($column));
    }

    /**
     * Determine if any model is "dirty" (i.e., has changed fields),
     * or a specific column is changed in any model.
     *
     * @param string|null $column Optional column name to check.
     * @return bool True if at least one model is dirty.
     */
    public function isDirty(?string $column = null): bool
    {
        return !$this->isClean($column);
    }

    /**
     * Batch update all models using their primary key as the match condition.
     *
     * @param array<array-key, mixed> $values Key-value pairs to update.
     * @return bool True on success.
     * @throws Exception If the update fails or keys are missing.
     */
    public function update(array $values): bool
    {
        $tableName  = (fn () => $this->{'tableName'})->call($this->model);
        $pdo        = (fn () => $this->{'pdo'})->call($this->model);
        $primaryKey = (fn () => $this->{'primaryKey'})->call($this->model);
        $update     = new Update($tableName, $pdo);

        $update->values($values)->in($primaryKey, $this->getPrimaryKey());

        return $update->execute();
    }

    /**
     * Batch delete all models from the database using their primary key.
     *
     * @return bool True on success.
     * @throws Exception If the delete fails.
     */
    public function delete(): bool
    {
        $tableName  = (fn () => $this->{'tableName'})->call($this->model);
        $pdo        = (fn () => $this->{'pdo'})->call($this->model);
        $primaryKey = (fn () => $this->{'primaryKey'})->call($this->model);
        $delete     = new Delete($tableName, $pdo);

        $delete->in($primaryKey, $this->getPrimaryKey());

        return $delete->execute();
    }

    /**
     * Convert the collection of models to an array of raw data arrays.
     * Each model is flattened to an associative array, then merged.
     *
     * @return array<array-key, mixed> Flat array of all model data.
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
