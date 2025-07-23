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

namespace Tests\Database\RealDatabase;

use Omega\Database\Query\Query;
use Omega\Database\Query\Join\InnerJoin;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;
use Tests\Database\Traits\UserTrait;

/**
 * Test suite for SELECT operations using the Query class.
 *
 * This class verifies the correct behavior of SELECT queries under various conditions,
 * including column selection, filtering, pagination, and joins.
 *
 * The tests cover:
 * - Selecting all or specific columns
 * - Applying WHERE, EQUAL, IN, BETWEEN, LIKE, and custom conditions
 * - Using LIMIT, OFFSET, and strict mode
 * - Executing JOINs with related tables
 *
 * Each test asserts that the expected data is retrieved accurately from the database.
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
#[CoversClass(InnerJoin::class)]
class SelectTest extends AbstractDatabase
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
     * Clean up the test environment after each test.
     *
     * This method flushes and resets the application container
     * to ensure a clean state between tests.
     *
     * @return void
     */
    private function profileFactory(): void
    {
        // factory
        $this->pdo
            ->query('CREATE TABLE profiles (
                user varchar(32) NOT NULL,
                real_name varchar(500) NOT NULL,
                PRIMARY KEY (user)
              )')
            ->execute();

        $this->pdo
            ->query('INSERT INTO profiles (
                user,
                real_name
              ) VALUES (
                :user,
                :real_name
              )')
            ->bind(':user', 'taylor')
            ->bind(':real_name', 'taylor otwell')
            ->execute();
    }

    /**
     * Test it can select query.
     *
     * @return void
     */
    public function testItCanSelectQuery(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('password', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
    }

    /**
     * Test it can select query only user.
     *
     * @return void
     */
    public function testItCanSelectQueryOnlyUser(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select(['user'])
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayNotHasKey('password', $users[0]);
        $this->assertArrayNotHasKey('stat', $users[0]);
    }

    /**
     * Test it can select query with between.
     *
     * @return void
     */
    public function testItCanSelectQueryWithBetween(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->between('stat', 0, 100)
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * Test it can select query with compare.
     *
     * @return void
     */
    public function testItCanSelectQueryWithCompare(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->compare('user', '=', 'taylor')
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * Test it can select query with equal.
     *
     * @return void
     */
    public function testItCanSelectQueryWithEqual(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->equal('user', 'taylor')
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * Test it can select query with in.
     *
     * @return void
     */
    public function testItCanSelectQueryWithIn()
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->in('user', ['taylor'])
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * Test it can select query with like.
     *
     * @return void
     */
    public function testItCanSelectQueryWithLike(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->like('user', 'tay%')
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * Test it can select query with where.
     *
     * @return void
     */
    public function testItCanSelectQueryWithWhere(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * Test it can select query with multi condition.
     *
     * @return void
     */
    public function testItCanSelectQueryWithMultiCondition(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->compare('stat', '>', 1)
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * Test it can select query with limit.
     *
     * @return void
     */
    public function testItCanSelectQueryWithLimit(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->limit(0, 1)
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('password', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
    }

    /**
     * Test it can select query with offset.
     *
     * @return void
     */
    public function testItCanSelectQueryWithOffset(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->limitStart(0)
            ->offset(1)
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('password', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
    }

    /**
     * Test it can select query with limit offset.
     *
     * @return void
     */
    public function testItCanSelectQueryWithLimitOffset(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->limitOffset(0, 10)
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('password', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
    }

    /**
     * est it can select query with strict mode.
     *
     * @return void
     */
    public function testItCanSelectQueryWithStrictMode(): void
    {
        $users = Query::from('users', $this->pdo)
            ->select()
            ->equal('user', 'taylor')
            ->equal('stat', 99)
            ->strictMode(false)
            ->all()
        ;

        $this->assertEquals('taylor', $users[0]['user']);
    }

    /**
     * Test it can select join.
     *
     * @return void
     */
    public function testItCanSelectJoin(): void
    {
        $this->profileFactory();

        $users = Query::from('users', $this->pdo)
            ->select()
            ->equal('user', 'taylor')
            ->join(InnerJoin::ref('profiles', 'user '))
            ->all()
        ;

        $this->assertArrayHasKey('user', $users[0]);
        $this->assertArrayHasKey('password', $users[0]);
        $this->assertArrayHasKey('stat', $users[0]);
        $this->assertArrayHasKey('real_name', $users[0]);
    }
}
