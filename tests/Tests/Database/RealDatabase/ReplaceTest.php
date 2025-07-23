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
 * Test suite for REPLACE operations using the Query class.
 *
 * This class verifies that the query builder can correctly perform REPLACE statements,
 * which either insert new records or overwrite existing ones based on primary or unique keys.
 *
 * The tests cover:
 * - Replacing non-existent (new) records
 * - Replacing existing records and updating their data
 * - Performing REPLACE with multiple rows in a single query
 *
 * Each test asserts the correct presence and values of user records in the database
 * after the operation.
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
class ReplaceTest extends AbstractDatabase
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
     * Test it can replace on new data.
     *
     * @return void
     */
    public function testItCanReplaceOnNewData(): void
    {
        Query::from('users', $this->pdo)
            ->replace()
            ->values([
                'user'      => 'adriano',
                'password'  => 'secret',
                'stat'      => 99,
            ])
            ->execute();

        $this->assertUserExist('adriano');
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
            ->replace()
            ->values([
                'user'      => 'adriano',
                'password'  => 'secret',
                'stat'      => 66,
            ])
            ->execute();

        $this->assertUserStat('adriano', 66);
    }

    /**
     * Test it can update insert using one query.
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
            ->replace()
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
            ->execute();

        $this->assertUserStat('adriano', 66);
        $this->assertUserExist('adriano2');
    }
}
