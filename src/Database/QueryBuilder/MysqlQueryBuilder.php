<?php

/**
 * Part of Omega CMS -  Database Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Database\QueryBuilder;

/*
 * @use
 */
use Omega\Database\Adapter\DatabaseAdapterInterface;
use Omega\Database\Adapter\MysqlAdapter;

/**
 * Mysql query builder class.
 *
 * This `MysqlQueryBuilder` class provides methods to build and execute MySQL queries. It extends
 * the abstract query builder class, implementing MySQL-specific functionality. It is designed to
 * work with the MySQLAdapter for database connections.
 *
 * @category    Omega
 * @package     Database
 * @subpackage  QueryBuilder
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class MysqlQueryBuilder extends AbstractQueryBuilder
{
    /**
     * MysqlQueryBuilder class constructor.
     *
     * @param DatabaseAdapterInterface $connection Holds an instance of the MySQLAdapter for database connection.
     *
     * @return void
     */
    public function __construct(
        protected DatabaseAdapterInterface $connection
    ) {
        parent::__construct($connection);
    }
}
