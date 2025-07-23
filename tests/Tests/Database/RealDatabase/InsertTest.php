<?php

declare(strict_types=1);

namespace Tests\Database\RealDatabase;

use Omega\Database\Query\Query;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;
use Tests\Database\Traits\UserTrait;

/**
 * Test suite for INSERT operations using the Query class.
 *
 * This class verifies that data can be correctly inserted into the database
 * using different modes and options, including:
 * - Basic single-row insertions
 * - Multi-row insertions
 * - Insert with "ON" clause for conditional updates
 * - Replacing existing data with new values
 *
 * Each test ensures that the data is correctly inserted or updated
 * and that the database reflects the expected state after execution.
 *
 * @category   Omega\Tests
 * @package    Databse
 * @subpackage RealDatabase
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Query::class)]
class InsertTest extends AbstractDatabase
{
    use UserTrait;

    /**
     * Set up the test environment before each test.
     *
     * This method is called before each test method is run.
     * Override it to initialize objects, mock dependencies, or reset state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->createConnection();
        $this->createUserSchema();
        $this->createUser([
            [
                'user'     => 'taylor',
                'password' => 'secret',
                'stat'     => 99,
            ],
        ]);
    }

    /**
     * Clean up the test environment after each test.
     *
     * This method flushes and resets the application container
     * to ensure a clean state between tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->dropConnection();
    }

    /**
     * Test it can insert data.
     *
     * @return void
     */
    public function testItCanInsertData()
    {
        Query::from('users', $this->pdo)
            ->insert()
            ->values([
                'user'      => 'adriano',
                'password'  => 'secret',
                'stat'      => 99,
            ])
            ->execute();

        $this->assertUserExist('adriano');
    }

    /**
     * Test it can insert multi raw.
     *
     * @return void
     */
    public function testItCanInsertMultiRaw(): void
    {
        Query::from('users', $this->pdo)
            ->insert()
            ->rows([
                [
                    'user'      => 'adriano',
                    'password'  => 'secret',
                    'stat'      => 1,
                ], [
                    'user'      => 'giovannini',
                    'password'  => 'secret',
                    'stat'      => 2,
                ],
            ])
            ->execute();

        $this->assertUserExist('adriano');
        $this->assertUserExist('giovannini');
    }

    /**
     * Test it can replace on exist data.
     *
     * @return void
     */
    public function testItCanReplaceOnExistData(): void
    {
        Query::from('users', $this->pdo)
            ->insert()
            ->values([
                'user'      => 'adriano',
                'password'  => 'secret',
                'stat'      => 99,
            ])
            ->execute();

        Query::from('users', $this->pdo)
            ->insert()
            ->values([
                'user'      => 'adriano',
                'password'  => 'secret',
                'stat'      => 66,
            ])
            ->on('stat')
            ->execute();

        $this->assertUserStat('adriano', 66);
    }

    /**
     *Test it can update insert using one query.
     *
     * @return void
     */
    public function testItCanUpdateInsertUsingOneQuery(): void
    {
        Query::from('users', $this->pdo)
            ->insert()
            ->values([
                'user'      => 'adriano',
                'password'  => 'secret',
                'stat'      => 99,
            ])
            ->execute();

        Query::from('users', $this->pdo)
            ->insert()
            ->rows([
                [
                    'user'      => 'adriano',
                    'password'  => 'secret',
                    'stat'      => 66,
                ],
                [
                    'user'      => 'adriano2',
                    'password'  => 'secret',
                    'stat'      => 66,
                ],
            ])
            ->on('user')
            ->on('stat')
            ->execute();

        $this->assertUserStat('adriano', 66);
        $this->assertUserExist('adriano2');
    }
}
