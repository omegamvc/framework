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
 * Test suite for validating SQL DELETE query generation using the Query builder.
 *
 * This class ensures that a wide range of DELETE statements are correctly built,
 * covering different conditions including equality, comparison, BETWEEN, IN,
 * LIKE, raw WHERE clauses, and strict mode behavior.
 * It also validates the generated raw SQL as well as the bound query with values.
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
class DeleteTest extends AbstractDatabaseQuery
{
    /**
     * Test it can delete between.
     *
     * @return void
     */
    public function testItCanDeleteBetween(): void
    {
        $delete = Query::from('test', $this->pdo)
            ->delete()
            ->between('column_1', 1, 100)
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE (test.column_1 BETWEEN :b_start AND :b_end)',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM test WHERE (test.column_1 BETWEEN 1 AND 100)',
            $delete->queryBind()
        );
    }

    /**
     * Test it can delete compare.
     *
     * @return void
     */
    public function testItCanDeleteCompare(): void
    {
        $delete = Query::from('test', $this->pdo)
            ->delete()
            ->compare('column_1', '=', 100)
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 = :column_1) )',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 = 100) )',
            $delete->queryBind()
        );
    }

    /**
     * Test it can delete equal.
     *
     * @return void
     */
    public function testItCanDeleteEqual(): void
    {
        $delete = Query::from('test', $this->pdo)
            ->delete()
            ->equal('column_1', 100)
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 = :column_1) )',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 = 100) )',
            $delete->queryBind()
        );
    }

    /**
     * Test it can delete in.
     *
     * @return void
     */
    public function testItCanDeleteIn(): void
    {
        $delete = Query::from('test', $this->pdo)
            ->delete()
            ->in('column_1', [1, 2])
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE (test.column_1 IN (:in_0, :in_1))',
            $delete->__toString()
        );

        $this->assertEquals(
            'DELETE FROM test WHERE (test.column_1 IN (1, 2))',
            $delete->queryBind()
        );
    }

    /**
     * Test it can delete like.
     *
     * @return void
     */
    public function testItCanDeleteLike(): void
    {
        $delete = Query::from('test', $this->pdo)
            ->delete()
            ->like('column_1', 'test')
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 LIKE :column_1) )',
            $delete->__toString()
        );

        $this->assertEquals(
            "DELETE FROM test WHERE ( (test.column_1 LIKE 'test') )",
            $delete->queryBind()
        );
    }

    /**
     * Test it can delete where.
     *
     * @return void
     */
    public function testItCanDeleteWhere(): void
    {
        $delete = Query::from('test', $this->pdo)
            ->delete()
            ->where('a < :a OR b > :b', [[':a', 1], [':b', 2]])
        ;

        $this->assertEquals(
            'DELETE FROM test WHERE a < :a OR b > :b',
            $delete->__toString(),
            'update with where statement is like'
        );

        $this->assertEquals(
            'DELETE FROM test WHERE a < 1 OR b > 2',
            $delete->queryBind(),
            'update with where statement is like'
        );
    }

    /**
     * Test it correct delete with strict off.
     *
     * @return void
     */
    public function testItCorrectDeleteWithStrictOff(): void
    {
        $delete = Query::from('test', $this->pdo)
            ->delete()
            ->equal('column_1', 123)
            ->equal('column_2', 'abc')
            ->strictMode(false);

        $this->assertEquals(
            'DELETE FROM test WHERE ( (test.column_1 = :column_1) OR (test.column_2 = :column_2) )',
            $delete->__toString(),
            'update statement must have using or statement'
        );

        $this->assertEquals(
            "DELETE FROM test WHERE ( (test.column_1 = 123) OR (test.column_2 = 'abc') )",
            $delete->queryBind(),
            'update statement must have using or statement'
        );
    }
}
