<?php

/**
 * Part of Omega -  Database Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\QueryBuilder;

use Omega\Database\Adapter\DatabaseAdapterInterface;

/**
 * Mysql query builder class.
 *
 * This `MysqlQueryBuilder` class provides methods to build and execute MySQL queries. It extends
 * the abstract query builder class, implementing MySQL-specific functionality. It is designed to
 * work with the MySQLAdapter for database connections.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Migration
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class MysqlQueryBuilder extends AbstractQueryBuilder
{
    /**
     * MysqlQueryBuilder class constructor.
     *
     * @param DatabaseAdapterInterface $connection Holds an instance of the MySQLAdapter for database connection.
     * @return void
     */
    public function __construct(
        protected DatabaseAdapterInterface $connection
    ) {
        parent::__construct($connection);
    }
}
