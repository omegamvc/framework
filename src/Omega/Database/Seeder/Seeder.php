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

namespace Omega\Database\Seeder;

use Omega\Database\Connection;
use Omega\Database\Query\Insert;

/**
 * Base class for database seeders.
 *
 * A seeder is used to populate tables with predefined data.
 * Concrete classes must implement the `run()` method to define
 * the seed logic. This class also provides helper methods to
 * simplify data insertion and chaining seeder execution.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Seeder
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
abstract class Seeder
{
    /**
     * The database connection instance.
     *
     * @var Connection
     */
    protected Connection $pdo;

    /**
     * Seeder constructor.
     *
     * @param Connection $pdo The PDO connection to be used for inserts.
     */
    public function __construct(Connection $pdo)
    {
        $this->pdo =  $pdo;
    }

    /**
     * Call another seeder class and execute its `run()` method.
     *
     * @param class-string $className The class name of the seeder to call.
     *
     * @return void
     */
    public function call(string $className): void
    {
        $class = new $className($this->pdo);
        $class->run();
    }

    /**
     * Create a new insert query builder for the given table.
     *
     * @param string $tableName The name of the table to insert into.
     *
     * @return Insert The insert query builder instance.
     */
    public function create(string $tableName): Insert
    {
        return new Insert($tableName, $this->pdo);
    }

    /**
     * Execute the seeder logic.
     *
     * This method must be implemented by all concrete seeders.
     *
     * @return void
     */
    abstract public function run(): void;
}
