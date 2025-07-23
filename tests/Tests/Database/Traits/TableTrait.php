<?php

/**
 * Part of Omega - Tests\Database Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Database\Traits;

use function PHPUnit\Framework\assertCount;

/**
 * Provides utility assertions for database-related tests.
 *
 * This trait includes helper methods used to validate
 * the existence of databases or other schema-level elements
 * during integration or functional tests.
 *
 * @category   Omega\Tests
 * @package    Database
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
trait TableTrait
{
    /**
     * Asserts that a database with the given name exists.
     *
     * Executes a `SHOW DATABASES LIKE` query and verifies that
     * exactly one result is returned, ensuring the database exists.
     *
     * @param string $databaseName The name of the database to check.
     * @return void
     */
    protected function assertDbExists(string $databaseName): void
    {
        $a = $this->pdo_schema->query('SHOW DATABASES LIKE ' . $databaseName)->resultset();

        assertCount(1, $a);
    }
}
