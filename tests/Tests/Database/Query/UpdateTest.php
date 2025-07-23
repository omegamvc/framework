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
 * Unit test suite for UPDATE query generation using the Query query builder.
 *
 * This class ensures that various forms of SQL UPDATE statements are correctly
 * built and rendered by the Query component, including their bound value representation.
 *
 * Covered features include:
 * - Basic `UPDATE ... SET` syntax
 * - Conditional clauses using BETWEEN, IN, LIKE, and comparison operators
 * - Complex WHERE expressions (including manual conditions)
 * - Logical concatenation (AND vs OR) via strict mode toggle
 *
 * Each test validates both the parameterized query (`__toString`) and the bound
 * value SQL string (`queryBind`) to ensure correctness of the query generation logic.
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
class UpdateTest extends AbstractDatabaseQuery
{
    /**
     * Test it con update between.
     *
     * @return void
     */
    public function testItCanUpdateBetween(): void
    {
        $update = Query::from('test', $this->pdo)
            ->update()
            ->value('a', 'b')
            ->between('column_1', 1, 100)
        ;

        $this->assertEquals(
            'UPDATE test SET a = :bind_a WHERE (test.column_1 BETWEEN :b_start AND :b_end)',
            $update->__toString()
        );

        $this->assertEquals(
            "UPDATE test SET a = 'b' WHERE (test.column_1 BETWEEN 1 AND 100)",
            $update->queryBind()
        );
    }

    /**
     * Test it can update compare.
     *
     * @return void
     */
    public function testItCanUpdateCompare(): void
    {
        $update = Query::from('test', $this->pdo)
            ->update()
            ->value('a', 'b')
            ->compare('column_1', '=', 100)
        ;

        $this->assertEquals(
            'UPDATE test SET a = :bind_a WHERE ( (test.column_1 = :column_1) )',
            $update->__toString()
        );

        $this->assertEquals(
            "UPDATE test SET a = 'b' WHERE ( (test.column_1 = 100) )",
            $update->queryBind()
        );
    }

    /**
     * Test it can update equal.
     *
     * @return void
     */
    public function testItCanUpdateEqual(): void
    {
        $update = Query::from('test', $this->pdo)
            ->update()
            ->value('a', 'b')
            ->equal('column_1', 100)
        ;

        $this->assertEquals(
            'UPDATE test SET a = :bind_a WHERE ( (test.column_1 = :column_1) )',
            $update->__toString()
        );

        $this->assertEquals(
            "UPDATE test SET a = 'b' WHERE ( (test.column_1 = 100) )",
            $update->queryBind()
        );
    }

    /**
     * Test it can update in.
     *
     * @return void
     */
    public function testItCanUpdateIn(): void
    {
        $update = Query::from('test', $this->pdo)
            ->update()
            ->value('a', 'b')
            ->in('column_1', [1, 2])
        ;

        $this->assertEquals(
            'UPDATE test SET a = :bind_a WHERE (test.column_1 IN (:in_0, :in_1))',
            $update->__toString()
        );

        $this->assertEquals(
            "UPDATE test SET a = 'b' WHERE (test.column_1 IN (1, 2))",
            $update->queryBind()
        );
    }

    /**
     * Test it can update like.
     *
     * @return void
     */
    public function testItCanUpdateLike(): void
    {
        $update = Query::from('test', $this->pdo)
            ->update()
            ->value('a', 'b')
            ->like('column_1', 'test')
        ;

        $this->assertEquals(
            'UPDATE test SET a = :bind_a WHERE ( (test.column_1 LIKE :column_1) )',
            $update->__toString()
        );

        $this->assertEquals(
            "UPDATE test SET a = 'b' WHERE ( (test.column_1 LIKE 'test') )",
            $update->queryBind()
        );
    }

    /**
     * Test it can update where.
     *
     * @return void
     */
    public function testItCanUpdateWhere(): void
    {
        $update = Query::from('test', $this->pdo)
            ->update()
            ->value('a', 'b')
            ->where('a < :a OR b > :b', [[':a', 1], [':b', 2]])
        ;

        $this->assertEquals(
            'UPDATE test SET a = :bind_a WHERE a < :a OR b > :b',
            $update->__toString(),
            'update with where statement is like'
        );

        $this->assertEquals(
            "UPDATE test SET a = 'b' WHERE a < 1 OR b > 2",
            $update->queryBind(),
            'update with where statement is like'
        );
    }

    /**
     * Test it correct update with strict off.
     *
     * @return void
     */
    public function testItCorrectUpdateWithStrictOff(): void
    {
        $update = Query::from('test', $this->pdo)
            ->update()
            ->value('a', 'b')
            ->equal('column_1', 123)
            ->equal('column_2', 'abc')
            ->strictMode(false);

        $this->assertEquals(
            'UPDATE test SET a = :bind_a WHERE ( (test.column_1 = :column_1) OR (test.column_2 = :column_2) )',
            $update->__toString()
        );

        $this->assertEquals(
            "UPDATE test SET a = 'b' WHERE ( (test.column_1 = 123) OR (test.column_2 = 'abc') )",
            $update->queryBind()
        );
    }
}
