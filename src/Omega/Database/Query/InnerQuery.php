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

use function trim;

/**
 * Represents an inline subquery or aliased table in SQL statements.
 *
 * The `InnerQuery` class wraps a `Select` query to be used as a subquery,
 * or simply stores an alias for a table name when not using subqueries.
 * It implements the `Stringable` interface to return a valid SQL fragment
 * like: `(SELECT ...) AS alias` or just `alias`.
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
final readonly class InnerQuery implements \Stringable
{
    /**
     * Create a new InnerQuery instance.
     *
     * @param Select|null $select The subquery SELECT instance (null if not a subquery)
     * @param string      $table  The alias or table name
     */
    public function __construct(private ?Select $select = null, private string $table = '')
    {
    }

    /**
     * Check if the instance represents a subquery.
     *
     * @return bool True if a SELECT instance is set; otherwise, false
     */
    public function isSubQuery(): bool
    {
        return null !== $this->select;
    }

    /**
     * Get the alias or table name used in SQL.
     *
     * @return string Alias name
     */
    public function getAlias(): string
    {
        return $this->table;
    }

    /**
     * Retrieve the parameter bindings from the underlying SELECT query.
     *
     * @return Bind[] Array of bound values
     */
    public function getBind(): array
    {
        return $this->select->getBinds();
    }

    /**
     * Convert the InnerQuery to a valid SQL string.
     *
     * If the object wraps a subquery, returns `(SELECT ...) AS alias`,
     * otherwise just the alias string.
     *
     * @return string SQL representation of the subquery or alias
     */
    public function __toString(): string
    {
        return $this->isSubQuery()
            ? '(' . trim((string) $this->select) . ') AS ' . $this->getAlias()
            : $this->getAlias();
    }
}
