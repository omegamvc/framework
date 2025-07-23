<?php

/** @noinspection PhpRedundantOptionalArgumentInspection */

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
 * Test suite for verifying the correct behavior of LIMIT and OFFSET clauses
 * in SELECT queries using the Query builder.
 *
 * This class ensures that the query builder handles different edge cases for LIMIT and OFFSET,
 * including negative values, combinations with ORDER BY, and different forms of limit syntax.
 * Both raw SQL with placeholders and the evaluated query with values are tested.
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
class LimitTest extends AbstractDatabaseQuery
{
    /**
     * Test it correct select query with limit order.
     *
     * @return void
     */
    public function testItCorrectSelectQueryWithLimitOrder(): void
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->between('column_1', 1, 100)
            ->limit(1, 10)
            ->order('column_1', Query::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN :b_start AND :b_end) ORDER BY test.column_1 ASC LIMIT 1, 10',
            $select->__toString(),
            'select with where statement is between'
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN 1 AND 100) ORDER BY test.column_1 ASC LIMIT 1, 10',
            $select->queryBind(),
            'select with where statement is between'
        );
    }

    /**
     * Test it correct select query with limit end order with limit end less than zero.
     *
     * @return void
     */
    public function testItCorrectSelectQueryWithLimitEndOrderWIthLimitEndLessThatZero(): void
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->between('column_1', 1, 100)
            ->limit(2, -1)
            ->order('column_1', Query::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN :b_start AND :b_end) ORDER BY test.column_1 ASC LIMIT 2, 0',
            $select->__toString(),
            'select with where statement is between'
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN 1 AND 100) ORDER BY test.column_1 ASC LIMIT 2, 0',
            $select->queryBind(),
            'select with where statement is between'
        );
    }

    /**
     * Test it correct select query with limit start less than zero.
     *
     * @return void
     */
    public function testItCorrectSelectQueryWithLimitStartLessThanZero(): void
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->between('column_1', 1, 100)
            ->limit(-1, 2)
            ->order('column_1', Query::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN :b_start AND :b_end) ORDER BY test.column_1 ASC LIMIT 2',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN 1 AND 100) ORDER BY test.column_1 ASC LIMIT 2',
            $select->queryBind()
        );
    }

    /**
     * Test it correct select query with limit and offset.
     *
     * @return void
     */
    public function testItCorrectSelectQueryWithLimitAndOffset(): void
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->between('column_1', 1, 100)
            ->limitStart(1)
            ->offset(10)
            ->order('column_1', Query::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN :b_start AND :b_end) ORDER BY test.column_1 ASC LIMIT 1 OFFSET 10',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN 1 AND 100) ORDER BY test.column_1 ASC LIMIT 1 OFFSET 10',
            $select->queryBind()
        );
    }

    /**
     * Test it correct select query with limit start and limit end less than zero.
     *
     * @return void
     */
    public function testItCorrectSelectQueryWithLimitStartAndLimitEndLessThanZero(): void
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->between('column_1', 1, 100)
            ->limit(-1, -1)
            ->order('column_1', Query::ORDER_ASC);

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN :b_start AND :b_end) ORDER BY test.column_1 ASC',
            $select->__toString()
        );
        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN :b_start AND :b_end) ORDER BY test.column_1 ASC',
            $select->__toString()
        );
    }
}
