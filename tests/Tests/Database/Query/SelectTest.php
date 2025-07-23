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
use Omega\Database\Query\InnerQuery;
use Omega\Database\Query\Select;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabaseQuery;

/**
 * Unit test suite for SELECT query generation using the Query query builder.
 *
 * This class tests the full range of SELECT clause capabilities provided by
 * the Query class, ensuring that generated SQL statements and their bound
 * representations are syntactically and semantically correct.
 *
 * Covered features include:
 * - Simple and complex WHERE conditions (equal, between, in, like, compare)
 * - Multi-column and grouped selects
 * - EXISTS and NOT EXISTS subqueries
 * - Subqueries as data sources (via InnerQuery)
 * - LIMIT, OFFSET, and ORDER clauses, including null-aware ordering
 * - Strict mode (AND vs OR concatenation)
 * - Grouping and multi-ordering capabilities
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
#[CoversClass(InnerQuery::class)]
#[CoversClass(Select::class)]
class SelectTest extends AbstractDatabaseQuery
{
    /**
     * Test it can select between.
     *
     * @return void
     */
    public function testItCanSelectBetween()
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->between('column_1', 1, 100)
        ;

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN :b_start AND :b_end)',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 BETWEEN 1 AND 100)',
            $select->queryBind()
        );
    }

    /**
     * Test it can select compare.
     *
     * @return void
     */
    public function testItCanSelectCompare()
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->compare('column_1', '=', 100)
        ;

        $this->assertEquals(
            'SELECT * FROM test WHERE ( (test.column_1 = :column_1) )',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE ( (test.column_1 = 100) )',
            $select->queryBind()
        );
    }

    /**
     * Test it can select equal.
     *
     * @return void
     */
    public function testItCanSelectEqual()
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->equal('column_1', 100)
        ;

        $this->assertEquals(
            'SELECT * FROM test WHERE ( (test.column_1 = :column_1) )',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE ( (test.column_1 = 100) )',
            $select->queryBind()
        );
    }

    /**
     * Test it can select in.
     *
     * @return void
     */
    public function testItCanSelectIn()
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->in('column_1', [1, 2])
        ;

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 IN (:in_0, :in_1))',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE (test.column_1 IN (1, 2))',
            $select->queryBind()
        );
    }

    /**
     * Test it can select like.
     *
     * @return void
     */
    public function testItCanSelectLike()
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->like('column_1', 'test')
        ;

        $this->assertEquals(
            'SELECT * FROM test WHERE ( (test.column_1 LIKE :column_1) )',
            $select->__toString()
        );

        $this->assertEquals(
            "SELECT * FROM test WHERE ( (test.column_1 LIKE 'test') )",
            $select->queryBind()
        );
    }

    /**
     * Test it can select where.
     *
     * @return void
     */
    public function testItCanSelectWhere()
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->where('a < :a OR b > :b', [[':a', 1], [':b', 2]])
        ;

        $this->assertEquals(
            'SELECT * FROM test WHERE a < :a OR b > :b',
            $select->__toString(),
            'select with where statement is like'
        );

        $this->assertEquals(
            'SELECT * FROM test WHERE a < 1 OR b > 2',
            $select->queryBind(),
            'select with where statement is like'
        );
    }

    /**
     * Test it correct select multi-column.
     *
     * @return void
     */
    public function testItCorrectSelectMultiColumn(): void
    {
        $select = Query::from('test', $this->pdo)
            ->select(['column_1', 'column_2', 'column_3'])
            ->equal('column_1', 123)
            ->equal('column_2', 'abc')
            ->equal('column_3', true);

        $this->assertEquals(
            'SELECT column_1, column_2, column_3 FROM test WHERE ( (test.column_1 = :column_1) AND (test.column_2 = :column_2) AND (test.column_3 = :column_3) )',
            $select->__toString(),
            'select statement must have 3 selected query'
        );

        $this->assertEquals(
            "SELECT column_1, column_2, column_3 FROM test WHERE ( (test.column_1 = 123) AND (test.column_2 = 'abc') AND (test.column_3 = true) )",
            $select->queryBind(),
            'select statement must have 3 selected query'
        );
    }

    /**
     * Test it correct select with strict off.
     *
     * @return void
     */
    public function testItCorrectSelectWithStrictOff(): void
    {
        $select = Query::from('test', $this->pdo)
            ->select(['column_1', 'column_2', 'column_3'])
            ->equal('column_1', 123)
            ->equal('column_2', 'abc')
            ->strictMode(false);

        $this->assertEquals(
            'SELECT column_1, column_2, column_3 FROM test WHERE ( (test.column_1 = :column_1) OR (test.column_2 = :column_2) )',
            $select,
            'select statement must have using or statement'
        );

        $this->assertEquals(
            "SELECT column_1, column_2, column_3 FROM test WHERE ( (test.column_1 = 123) OR (test.column_2 = 'abc') )",
            $select->queryBind(),
            'select statement must have using or statement'
        );
    }

    /**
     * Test it can generate where exist query.
     *
     * @return void
     */
    public function testItCanGenerateWhereExistQuery(): void
    {
        $select = Query::from('base_1', $this->pdo)
            ->select()
            ->whereExist(
                (new Select('base_2', ['*'], $this->pdo))
                    ->equal('test', 'success')
                    ->where('base_1.id = base_2.id')
            )
            ->limit(1, 10)
            ->order('id', Query::ORDER_ASC)
        ;

        $this->assertEquals(
            'SELECT * FROM base_1 WHERE EXISTS ( SELECT * FROM base_2 WHERE ( (base_2.test = :test) ) AND base_1.id = base_2.id ) ORDER BY base_1.id ASC LIMIT 1, 10',
            $select->__toString(),
            'where exist query'
        );

        $this->assertEquals(
            "SELECT * FROM base_1 WHERE EXISTS ( SELECT * FROM base_2 WHERE ( (base_2.test = 'success') ) AND base_1.id = base_2.id ) ORDER BY base_1.id ASC LIMIT 1, 10",
            $select->queryBind(),
            'where exist query'
        );
    }

    /**
     * Test it can generate where not exist query.
     *
     * @return void
     */
    public function testItCanGenerateWhereNotExistQuery(): void
    {
        $select = Query::from('base_1', $this->pdo)
            ->select()
            ->whereNotExist(
                (new Select('base_2', ['*'], $this->pdo))
                    ->equal('test', 'success')
                    ->where('base_1.id = base_2.id')
            )
            ->limit(1, 10)
            ->order('id', Query::ORDER_ASC)
        ;

        $this->assertEquals(
            'SELECT * FROM base_1 WHERE NOT EXISTS ( SELECT * FROM base_2 WHERE ( (base_2.test = :test) ) AND base_1.id = base_2.id ) ORDER BY base_1.id ASC LIMIT 1, 10',
            $select->__toString(),
            'where exist query'
        );

        $this->assertEquals(
            "SELECT * FROM base_1 WHERE NOT EXISTS ( SELECT * FROM base_2 WHERE ( (base_2.test = 'success') ) AND base_1.id = base_2.id ) ORDER BY base_1.id ASC LIMIT 1, 10",
            $select->queryBind(),
            'where exist query'
        );
    }

    /**
     * Test it can generate select with where query.
     *
     * @return void
     */
    public function testItCanGenerateSelectWithWhereQuery(): void
    {
        $select = Query::from('base_1', $this->pdo)
            ->select()
            ->whereClause(
                'user =',
                (new Select('base_2', ['*'], $this->pdo))
                    ->equal('test', 'success')
                    ->where('base_1.id = base_2.id')
            )
            ->limit(1, 10)
            ->order('id', Query::ORDER_ASC)
        ;

        $this->assertEquals(
            'SELECT * FROM base_1 WHERE user = ( SELECT * FROM base_2 WHERE ( (base_2.test = :test) ) AND base_1.id = base_2.id ) ORDER BY base_1.id ASC LIMIT 1, 10',
            $select->__toString(),
            'where exist query'
        );

        $this->assertEquals(
            "SELECT * FROM base_1 WHERE user = ( SELECT * FROM base_2 WHERE ( (base_2.test = 'success') ) AND base_1.id = base_2.id ) ORDER BY base_1.id ASC LIMIT 1, 10",
            $select->queryBind(),
            'where exist query'
        );
    }

    /**
     * Test it can generate select with sub query.
     *
     * @return void
     */
    public function testItCanGenerateSelectWithSubQuery(): void
    {
        $select = Query::from(
            new InnerQuery(
                (new Select('base_2', ['id'], $this->pdo))
                    ->in('test', ['success']),
                'user'
            ),
            $this->pdo
        )
            ->select(['user.id as id'])
            ->limit(1, 10)
            ->order('id', Query::ORDER_ASC)
        ;

        $this->assertEquals(
            'SELECT user.id as id FROM (SELECT id FROM base_2 WHERE (base_2.test IN (:in_0))) AS user ORDER BY user.id ASC LIMIT 1, 10',
            $select->__toString(),
            'where exist query'
        );

        $this->assertEquals(
            "SELECT user.id as id FROM (SELECT id FROM base_2 WHERE (base_2.test IN ('success'))) AS user ORDER BY user.id ASC LIMIT 1, 10",
            $select->queryBind(),
            'where exist query'
        );
    }

    /**
     * Test it can select with group by.
     *
     * @return void
     */
    public function testItCanSelectWithGroupBy(): void
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->groupBy('column_1')
        ;
        $select_multi = Query::from('test', $this->pdo)
            ->select()
            ->groupBy('column_1', 'column_2')
        ;

        $this->assertEquals(
            'SELECT * FROM test GROUP BY column_1',
            $select->__toString()
        );

        $this->assertEquals(
            'SELECT * FROM test GROUP BY column_1, column_2',
            $select_multi->__toString()
        );
    }

    /**
     * Test it can generate multi order.
     *
     * @return void
     */
    public function testItCanGenerateMultiOrder(): void
    {
        $select = Query::from('base_1', $this->pdo)
            ->select()
            ->order('id', Query::ORDER_ASC)
            ->order('name', Query::ORDER_DESC)
        ;

        $this->assertEquals(
            'SELECT * FROM base_1 ORDER BY base_1.id ASC, base_1.name DESC',
            $select->__toString(),
            'order by query'
        );
    }

    /**
     * Test it can select with order if not null.
     *
     * @return void
     */
    public function testItCanSelectWithOrderIfNotNull(): void
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->orderIfNotNull('column_1');

        $this->assertEquals(
            'SELECT * FROM test ORDER BY test.column_1 IS NOT NULL ASC',
            $select->__toString()
        );
    }

    /**
     * Test it can select with order if null.
     *
     * @return void
     */
    public function testItCanSelectWithOrderIfNull(): void
    {
        $select = Query::from('test', $this->pdo)
            ->select()
            ->orderIfNull('column_1');

        $this->assertEquals(
            'SELECT * FROM test ORDER BY test.column_1 IS NULL ASC',
            $select->__toString()
        );
    }
}
