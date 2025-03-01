<?php

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

use Omega\Database\Exception\QueryException;
use Omega\Database\QueryBuilder\AbstractQueryBuilder;

use function is_null;

/**
 * Model collector class.
 *
 * The `ModelCollector` class is responsible for collecting query and transforming them into
 * model instance,  This class acts as a bridge between the query builder and model instances,
 * facilitating the transformation of raw database results into instances of the specified model
 * class.
 *
 * @category   Omega
 * @package    Database
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class ModelCollector
{
    /**
     * ModelCollector class constructor.
     *
     * @param AbstractQueryBuilder $builder Holds the query builder instance.
     * @param string               $class   Holds the fully qualified class name of the model.
     * @return void
     */
    public function __construct(
        private AbstractQueryBuilder $builder,
        private readonly string $class
    ) {
    }

    /**
     * Magic method to forward undefined method calls to the underlying query builder instance.
     *
     * This method allows dynamic method calls on the ModelCollector, delegating them to the
     * underlying query builder instance. This is particularly useful for building queries fluently.
     *
     * @param string                   $method     Holds the method name.
     * @param array<int|string, mixed> $parameters Holds the method parameters.
     * @return $this|mixed Return $this if the method is fluent, otherwise, returns the method result.
     */
    public function __call(string $method, array $parameters = []): mixed
    {
        $result = $this->builder->$method(...$parameters);

        if ($result instanceof AbstractQueryBuilder) {
            $this->builder = $result;

            return $this;
        }

        return $result;
    }

    /**
     * Retrieve the first result from the query and transform it into an instance of the model.
     * This method executes the query and returns the first result as an instance of the specified model class.
     *
     * @return mixed[] Return an instance of the model, or null if no results are found.
     * @throws QueryException
     */
    public function first(): mixed
    {
        $class = $this->class;
        $row   = $this->builder->first();

        if (! is_null($row)) {
            $row = $class::with($row);
        }

        return $row;
    }

    /**
     * Retrieve all results from the query and transform each into an instance of the model.
     * This method executes the query and returns an array of instances of the specified model class.
     *
     * @return array Return an array of model instances, or an empty array if no results are found.
     * @throws QueryException
     */
    public function all(): array
    {
        $class = $this->class;
        $rows  = $this->builder->all();

        foreach ($rows as $i => $row) {
            $rows[$i] = $class::with($row);
        }

        return $rows;
    }
}
