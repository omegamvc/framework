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
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;
use Tests\Database\Traits\UserTrait;

/**
 * Test suite for UPDATE operations using the Query class.
 *
 * This class verifies the ability of the query builder to construct and execute
 * UPDATE statements under various conditions and constraints.
 *
 * The tests cover:
 * - Simple value updates
 * - Conditional updates using BETWEEN, IN, LIKE, EQUAL, COMPARE, and WHERE clauses
 * - Combining multiple conditions in a single query
 *
 * Each test ensures that the specified field(s) are correctly updated
 * and that changes are reflected in the database as expected.
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
class UpdateTest extends AbstractDatabase
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
     * Test it can update.
     *
     * @return void
     */
    public function testItCanUpdate(): void
    {
        Query::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * Test it an update with between.
     *
     * @return void
     */
    public function testItCanUpdateWithBetween()
    {
        Query::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->between('stat', 0, 100)
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * Test it can update with compare.
     *
     * @return void
     */
    public function testItCanUpdateWithCompare(): void
    {
        Query::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->compare('user', '=', 'taylor')
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * Test it can update with equal.
     *
     * @return void
     */
    public function testItCanUpdateWithEqual(): void
    {
        Query::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->equal('user', 'taylor')
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * Test it can update with in.
     *
     * @return void
     */
    public function testItCanUpdateWithIn(): void
    {
        Query::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->in('user', ['taylor'])
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * Test it can update with like.
     *
     * @return void
     */
    public function testItCanUpdateWithLike(): void
    {
        Query::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->like('user', 'tay%')
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * est it can update with where.
     *
     * @return void
     */
    public function testItCanUpdateWithWhere(): void
    {
        Query::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }

    /**
     * Teest it can update with multi condition.
     *
     * @return void
     */
    public function testItCanUpdateWithMultiCondition(): void
    {
        Query::from('users', $this->pdo)
            ->update()
            ->value('stat', 0)
            ->compare('stat', '>', 1)
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->execute()
        ;

        $this->assertUserStat('taylor', 0);
    }
}
