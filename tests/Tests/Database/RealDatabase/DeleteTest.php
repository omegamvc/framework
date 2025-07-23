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
 * Test suite for the DELETE functionality of the Query class.
 *
 * This class verifies that the query builder can correctly generate and execute
 * DELETE statements using various filtering methods such as:
 * - Basic delete
 * - WHERE clauses
 * - BETWEEN conditions
 * - IN clauses
 * - LIKE patterns
 * - EQUAL and COMPARE filters
 * - Multi-condition combinations
 *
 * Each test ensures that the expected records are removed from the database.
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
class DeleteTest extends AbstractDatabase
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
     * Test it can delete.
     *
     * @return void
     */
    public function testItCanDelete(): void
    {
        Query::from('users', $this->pdo)
            ->delete()
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * Test it can delete with between.
     *
     * @return void
     */
    public function testItCanDeleteWithBetween(): void
    {
        Query::from('users', $this->pdo)
            ->delete()
            ->between('stat', 0, 100)
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * Test it can delete with compare.
     *
     * @return void
     */
    public function testItCanDeleteWithCompare(): void
    {
        Query::from('users', $this->pdo)
            ->delete()
            ->compare('user', '=', 'taylor')
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * Test it can delete with equal.
     *
     * @return void
     */
    public function testItCanDeleteWithEqual(): void
    {
        Query::from('users', $this->pdo)
            ->delete()
            ->equal('user', 'taylor')
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * Test it can delete with in.
     *
     * @return void
     */
    public function testItCanDeleteWithIn(): void
    {
        Query::from('users', $this->pdo)
            ->delete()
            ->in('user', ['taylor'])
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * Test it can delete with like.
     *
     * @return void
     */
    public function testItCanDeleteWithLike(): void
    {
        Query::from('users', $this->pdo)
            ->delete()
            ->like('user', 'tay%')
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * Test it can delete with where.
     *
     * @return void
     */
    public function testItCanDeleteWithWhere(): void
    {
        Query::from('users', $this->pdo)
            ->delete()
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }

    /**
     * Test it can delete with multi condition.
     *
     * @return void
     */
    public function testItCanDeleteWithMultiCondition(): void
    {
        Query::from('users', $this->pdo)
            ->delete()
            ->compare('stat', '>', 1)
            ->where('user = :user', [
                [':user', 'taylor'],
            ])
            ->execute()
        ;

        $this->assertUserNotExist('taylor');
    }
}
