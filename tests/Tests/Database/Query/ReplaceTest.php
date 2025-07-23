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

namespace Tests\Database\Query;

use Omega\Database\Query\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabaseQuery;

/**
 * Unit test for the REPLACE query builder feature in the Query class.
 *
 * This test suite verifies that REPLACE statements are properly generated
 * with different input methods, including:
 * - Single value inserts
 * - Bulk value inserts using `values()` and `value()`
 * - Multiple row inserts using `rows()`
 *
 * It ensures both the SQL with parameter placeholders (`__toString()`)
 * and the bound parameter interpolation (`queryBind()`) return the expected
 * REPLACE INTO SQL string.
 *
 * @category   Omega\Tests
 * @package    Database
 * @subpackage Schema
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Query::class)]
class ReplaceTest extends AbstractDatabaseQuery
{
    /**
     * Test it correct insert.
     *
     * @return void
     */
    public function testItCorrectInsert(): void
    {
        $insert = Query::from('test', $this->pdo)
            ->replace()
            ->value('a', 1)
        ;

        $this->assertEquals(
            'REPLACE INTO test (a) VALUES (:bind_a)',
            $insert->__toString()
        );

        $this->assertEquals(
            'REPLACE INTO test (a) VALUES (1)',
            $insert->queryBind()
        );
    }

    /**
     * Test it correct insert values.
     *
     * @return void
     */
    public function testItCorrectInsertValues(): void
    {
        $insert = Query::from('test', $this->pdo)
            ->replace()
            ->values([
                'a' => 'b',
                'c' => 'd',
                'e' => 'f',
            ])
        ;

        $this->assertEquals(
            'REPLACE INTO test (a, c, e) VALUES (:bind_a, :bind_c, :bind_e)',
            $insert->__toString()
        );

        $this->assertEquals(
            "REPLACE INTO test (a, c, e) VALUES ('b', 'd', 'f')",
            $insert->queryBind()
        );
    }

    /**
     * Test it correct insert query multi values.
     *
     * @return void
     */
    public function testItCorrectInsertQueryMultiValues(): void
    {
        $insert = Query::from('test', $this->pdo)
            ->replace()
            ->values([
                'a' => 'b',
                'c' => 'd',
                'e' => 'f',
            ])
            ->value('g', 'h')
        ;

        $this->assertEquals(
            'REPLACE INTO test (a, c, e, g) VALUES (:bind_a, :bind_c, :bind_e, :bind_g)',
            $insert->__toString()
        );

        $this->assertEquals(
            "REPLACE INTO test (a, c, e, g) VALUES ('b', 'd', 'f', 'h')",
            $insert->queryBind()
        );
    }

    /**
     * Test it correct insert query multi raws.
     *
     * @return void
     */
    public function testItCorrectInsertQueryMultiRaws(): void
    {
        $insert = Query::from('test', $this->pdo)
            ->replace()
            ->rows([
                [
                    'a' => 'b',
                    'c' => 'd',
                    'e' => 'f',
                ], [
                    'a' => 'b',
                    'c' => 'd',
                    'e' => 'f',
                ],
            ]);

        $this->assertEquals(
            'REPLACE INTO test (a, c, e) VALUES (:bind_0_a, :bind_0_c, :bind_0_e), (:bind_1_a, :bind_1_c, :bind_1_e)',
            $insert->__toString()
        );

        $this->assertEquals(
            "REPLACE INTO test (a, c, e) VALUES ('b', 'd', 'f'), ('b', 'd', 'f')",
            $insert->queryBind()
        );
    }
}
