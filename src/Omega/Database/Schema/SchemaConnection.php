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

namespace Omega\Database\Schema;

use Exception;
use Omega\Database\Connection;

/**
 * Specialized database connection for schema-level operations.
 *
 * Unlike the standard Connection class which connects to a specific database,
 * SchemaConnection establishes a connection only to the database server (host),
 * without targeting any specific schema. This allows execution of global commands
 * such as CREATE DATABASE or DROP DATABASE.
 *
 * Typically used internally by the Schema manager during table or database operations.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Schema
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class SchemaConnection extends Connection
{
    /**
     * Initialize a schema-level connection (no specific database selected).
     *
     * @param array<string, string> $config Connection parameters:
     *                                      - host:     the database host
     *                                      - user:     the username
     *                                      - password: the password
     *
     * @throws Exception if connection fails
     */
    public function __construct(array $config)
    {
        $host = $config['host'];
        $user = $config['user'];
        $pass = $config['password'];

        $this->config = $config;
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $this->useDsn($dsn, $user, $pass);
    }
}
