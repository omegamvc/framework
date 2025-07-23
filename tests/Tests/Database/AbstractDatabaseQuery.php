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

namespace Tests\Database;

use Omega\Database\Connection;
use Omega\Database\Schema\SchemaConnection;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Abstract base class for unit tests involving database query logic.
 *
 * Provides mock instances of Connection and SchemaConnection for isolated testing,
 * without relying on actual database connections.
 *
 * Designed to be extended by tests that require mocking of database behavior.
 *
 * @category  Omega\Tests
 * @package   Database
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversNothing]
abstract class AbstractDatabaseQuery extends TestCase
{
    /** @var Connection&MockObject Mocked instance of the custom Connection class. */
    protected Connection $pdo;

    /** @var SchemaConnection&MockObject Mocked instance of the schema-aware SchemaConnection class. */
    protected SchemaConnection $pdo_schema;

    /**
     * Set up the test environment before each test.
     *
     * Creates mock objects for Connection and SchemaConnection to avoid real database interaction.
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->pdo        = $this->createMock(Connection::class);
        $this->pdo_schema = $this->createMock(SchemaConnection::class);
    }
}
