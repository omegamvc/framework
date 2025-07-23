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
 * Test suite for validating SQL INSERT query generation using the Query builder.
 *
 * This class ensures that various INSERT statement features are correctly generated,
 * including single and multiple value inserts, merging values through method chaining,
 * bulk insert with multiple rows, and support for "ON DUPLICATE KEY UPDATE" clauses.
 * It verifies both the generated SQL with placeholders and the fully bound query string.
/**
 * Test suite for validating SQL INSERT query generation using the Query builder.
 *
 * This class ensures that various INSERT statement features are correctly generated,
 * including single and multiple value inserts, merging values through method chaining,
 * bulk insert with multiple rows, and support for "ON DUPLICATE KEY UPDATE" clauses.
 * It verifies both the generated SQL with placeholders and the fully bound query string.
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
class InsertTest extends AbstractDatabaseQuery
{
    /**
     * Test it correct insert.
     *
     * @return void
     */
    public function testItCorrectInsert(): void
    {
        $insert = Query::from('test', $this->pdo)
            ->insert()
            ->value('a', 1)
        ;

        $this->assertEquals(
            'INSERT INTO test (a) VALUES (:bind_a)',
            $insert->__toString()
        );

        $this->assertEquals(
            'INSERT INTO test (a) VALUES (1)',
            $insert->queryBind()
        );
    }

    /**
     * Test it can correct insert value.
     *
     * @return void
     */
    public function testItCorrectInsertValues(): void
    {
        $insert = Query::from('test', $this->pdo)
            ->insert()
            ->values([
                'a' => 'b',
                'c' => 'd',
                'e' => 'f',
            ])
        ;

        $this->assertEquals(
            'INSERT INTO test (a, c, e) VALUES (:bind_a, :bind_c, :bind_e)',
            $insert->__toString()
        );

        $this->assertEquals(
            "INSERT INTO test (a, c, e) VALUES ('b', 'd', 'f')",
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
            ->insert()
            ->values([
                'a' => 'b',
                'c' => 'd',
                'e' => 'f',
            ])
            ->value('g', 'h')
        ;

        $this->assertEquals(
            'INSERT INTO test (a, c, e, g) VALUES (:bind_a, :bind_c, :bind_e, :bind_g)',
            $insert->__toString()
        );

        $this->assertEquals(
            "INSERT INTO test (a, c, e, g) VALUES ('b', 'd', 'f', 'h')",
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
            ->insert()
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
            'INSERT INTO test (a, c, e) VALUES (:bind_0_a, :bind_0_c, :bind_0_e), (:bind_1_a, :bind_1_c, :bind_1_e)',
            $insert->__toString()
        );

        $this->assertEquals(
            "INSERT INTO test (a, c, e) VALUES ('b', 'd', 'f'), ('b', 'd', 'f')",
            $insert->queryBind()
        );
    }

    /**
     * Test it correct insert on duplicate key update.
     *
     * @return void
     */
    public function testItCorrectInsertOnDuplicateKeyUpdate(): void
    {
        $insert = Query::from('test', $this->pdo)
            ->insert()
            ->value('a', 1)
            ->on('a')
        ;

        $this->assertEquals(
            'INSERT INTO test (a) VALUES (:bind_a) ON DUPLICATE KEY UPDATE a = VALUES(a)',
            $insert->__toString()
        );

        $this->assertEquals(
            'INSERT INTO test (a) VALUES (1) ON DUPLICATE KEY UPDATE a = VALUES(a)',
            $insert->queryBind()
        );
    }
}
