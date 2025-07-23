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

namespace Tests\Database\Query\Schema\Table;

use Omega\Database\Schema\Table\Raw;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabaseQuery;

/**
 * Test suite for validating raw SQL query execution via the Raw schema builder.
 *
 * This class ensures that raw SQL statements passed to the Raw schema class
 * are preserved and rendered exactly as provided. It verifies the correct handling
 * of unparsed SQL definitions, enabling advanced or custom schema operations.
 *
 * @category   Omega\Tests
 * @package    Database
 * @subpackage Schema\Table
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Raw::class)]
class RawTest extends AbstractDatabaseQuery
{
    /**
     * Test it can generate query using add column.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingAddColumn(): void
    {
        $schema = new Raw('CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) )', $this->pdo_schema);

        $this->assertEquals(
            'CREATE TABLE testing_db.test ( PersonID int, LastName varchar(255), PRIMARY KEY (PersonID) )',
            $schema->__toString()
        );
    }
}
