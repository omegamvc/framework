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

use Omega\Database\Schema\Table\Create;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabaseQuery;

/**
 * Test suite for verifying support of various SQL data types during CREATE TABLE generation.
 *
 * This class ensures that the Create schema builder can correctly generate SQL syntax
 * for a range of data types, including enumerated types such as ENUM.
 * The tests focus on correct column type declaration and formatting in the final SQL output.
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
#[CoversClass(Create::class)]
class DataTypesTest extends AbstractDatabaseQuery
{
    /**
     * Test it can generate query using add column.
     *
     * @return void
     */
    public function testItCanGenerateQueryUsingAddColumn(): void
    {
        $schema = new Create('testing_db', 'test', $this->pdo_schema);
        $schema('name')->varchar(40);
        $schema('size')->enum(['x-small', 'small', 'medium', 'large', 'x-large']);

        $this->assertEquals(
            "CREATE TABLE testing_db.test ( name varchar(40), size ENUM ('x-small', 'small', 'medium', 'large', 'x-large') )",
            $schema->__toString()
        );
    }
}
