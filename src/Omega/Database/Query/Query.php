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

use Omega\Database\Connection;

/**
 * Query builder factory class.
 *
 * This class is responsible for instantiating new `Table` builders to construct SQL queries.
 * It supports dynamic table assignment via method calls, invocation, and static access,
 * and defines constants for ordering results.
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
class Query
{
    /** Constant representing ascending sort order. */
    public const int ORDER_ASC = 0;

    /** Constant representing descending sort order. */
    public const int ORDER_DESC = 1;

    /**
     * The database connection instance.
     *
     * @var Connection
     */
    protected Connection $pdo;

    /**
     * Initialize the query builder with a PDO connection.
     *
     * @param Connection $pdo The PDO connection to use
     */
    public function __construct(Connection $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Create a new query builder instance via function invocation.
     *
     * Allows use of `$query('table')` syntax as shorthand for `$query->table('table')`.
     *
     * @param string $tableName The table name
     * @return Table The table query builder instance
     */
    public function __invoke(string $tableName): Table
    {
        return $this->table($tableName);
    }

    /**
     * Create a new `Table` builder for a given table or subquery.
     *
     * @param string|InnerQuery $tableName Table name or subquery
     * @return Table The table query builder instance
     */
    public function table(string|InnerQuery $tableName): Table
    {
        return new Table($tableName, $this->pdo);
    }

    /**
     * Static method to create a `Table` builder from a table or subquery.
     *
     * Acts as a shortcut for:
     * ```php
     * Query::from('users', $pdo);
     * ```
     *
     * @param string|InnerQuery $tableName Table name or subquery
     * @param Connection        $pdo       The PDO connection to use
     * @return Table The table query builder instance
     */
    public static function from(string|InnerQuery $tableName, Connection $pdo): Table
    {
        $conn = new Query($pdo);

        return $conn->table($tableName);
    }
}
