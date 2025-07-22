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

namespace Omega\Database\Query;

use Omega\Collection\Collection;

/**
 * Abstract base class for query builders that fetch data from the database.
 *
 * Provides shared logic to retrieve data using SELECT queries and convert them into
 * various formats such as collections, arrays, or single records.
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
abstract class AbstractFetch extends AbstractQuery
{
    /**
     * Retrieve all matching records as a collection.
     *
     * @return Collection<string|int, mixed>|null A collection of results, or null if no results found.
     */
    public function get(): ?Collection
    {
        if (false === ($items = $this->all())) {
            $items = [];
        }

        return new Collection($items);
    }

    /**
     * Retrieve the first matching record.
     *
     * Executes the query and returns the first result as an associative array.
     *
     * @return array<string|int, mixed> The first record, or an empty array if none found.
     */
    public function single(): mixed
    {
        $this->builder();

        $this->pdo->query($this->query);
        foreach ($this->binds as $bind) {
            if (!$bind->hasBind()) {
                $this->pdo->bind($bind->getBind(), $bind->getValue());
            }
        }

        $result = $this->pdo->single();

        return $result === false ? [] : $this->pdo->single();
    }

    /**
     * Retrieve all matching records as an array.
     *
     * Executes the query and returns all results as an array of associative arrays.
     *
     * @return array<string|int, mixed>|false Array of results, or false on failure.
     */
    public function all(): array|false
    {
        $this->builder();

        $this->pdo->query($this->query);
        foreach ($this->binds as $bind) {
            if (!$bind->hasBind()) {
                $this->pdo->bind($bind->getBind(), $bind->getValue());
            }
        }

        return $this->pdo->resultSet();
    }
}
