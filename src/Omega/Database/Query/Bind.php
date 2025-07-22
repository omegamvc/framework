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

/**
 * Represents a named parameter binding for SQL queries.
 *
 * This class encapsulates a bind key, its associated value, an optional column name,
 * and a configurable prefix (default `:`) to construct named placeholders for prepared statements.
 *
 * Used to manage and manipulate SQL bind parameters consistently throughout the query builder.
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
final class Bind
{
    /**
     * The name of the bind placeholder (without prefix).
     *
     * Example: "user_id" would become ":user_id" with the default prefix.
     *
     * @var string
     */
    private string $bind;

    /**
     * The value associated with the bind name.
     *
     * This can be any scalar or null, depending on the context.
     *
     * @var mixed
     */
    private mixed $bindValue;

    /**
     * The optional name of the database column this bind relates to.
     *
     * Used for debugging, filtering, or mapping logic.
     *
     * @var string
     */
    private string $columnName;

    /**
     * The prefix used for the bind name in SQL.
     *
     * Defaults to ":".
     *
     * @var string
     */
    private string $prefixBind;

    /**
     * Bind constructor.
     *
     * @param string $bind        The bind name (without prefix)
     * @param mixed  $value       The value to be bound
     * @param string $columnName  Optional column name associated with the bind
     * @return void
     */
    public function __construct(string $bind, mixed $value, string $columnName = '')
    {
        $this->bind       = $bind;
        $this->bindValue  = $value;
        $this->columnName = $columnName;
        $this->prefixBind = ':';
    }

    /**
     * Create a new Bind instance with the given bind name and value.
     *
     * @param string $bind        The bind name (without prefix)
     * @param mixed  $value       The value to bind
     * @param string $columnName  Optional column name for reference
     * @return self
     */
    public static function set(string $bind, mixed $value, string $columnName = ''): self
    {
        return new Bind($bind, $value, $columnName);
    }

    /**
     * Set the prefix used before the bind name.
     *
     * Default is ':' (e.g. ":id").
     *
     * @param string $prefix
     * @return self
     */
    public function prefixBind(string $prefix): self
    {
        $this->prefixBind = $prefix;

        return $this;
    }

    /**
     * Update the bind name.
     *
     * @param string $bind
     * @return self
     */
    public function setBind(string $bind): self
    {
        $this->bind = $bind;

        return $this;
    }

    /**
     * Update the value to be bound.
     *
     * @param mixed $bindValue
     * @return self
     */
    public function setValue(mixed $bindValue): self
    {
        $this->bindValue = $bindValue;

        return $this;
    }

    /**
     * Set the name of the column associated with this bind.
     *
     * @param string $columnName
     * @return self
     */
    public function setColumnName(string $columnName): self
    {
        $this->columnName = $columnName;

        return $this;
    }

    /**
     * Get the full bind string with prefix (e.g. ":user_id").
     *
     * @return string
     */
    public function getBind(): string
    {
        return $this->prefixBind . $this->bind;
    }

    /**
     * Get the value associated with the bind.
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->bindValue;
    }

    /**
     * Get the associated column name.
     *
     * @return string
     */
    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * Check if the bind has an associated column name.
     *
     * @return bool
     */
    public function hasColumName(): bool
    {
        return '' !== $this->columnName;
    }

    /**
     * Mark the bind as representing a column (sets column name to bind name).
     *
     * @return self
     */
    public function markAsColumn(): self
    {
        $this->columnName = $this->bind;

        return $this;
    }

    /**
     * Check if the bind name is empty (i.e. not usable).
     *
     * @return bool
     */
    public function hasBind(): bool
    {
        return '' === $this->bind;
    }
}
