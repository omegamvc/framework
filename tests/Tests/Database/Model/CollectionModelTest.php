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

namespace Tests\Database\Model;

use Exception;
use Tests\Database\AbstractDatabase;

use function password_hash;

use const PASSWORD_DEFAULT;

/**
 * CollectionModelTest
 *
 * This class contains tests for collection-based model operations.
 * It tests CRUD operations on collections of User models,
 * including reading, updating, deleting multiple items,
 * and batch updates/deletes via single queries.
 *
 * Each test ensures that the collection behaves as expected
 * and that operations propagate correctly to individual models.
 *
 * @category   Omega\Tests
 * @package    Database
 * @subpackage Model
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
class CollectionModelTest extends AbstractDatabase
{
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
        $password = password_hash('password', PASSWORD_DEFAULT);
        $this->createUser([
            [
                'user'     => 'nuno',
                'password' => $password,
                'stat'     => 90,
            ],
            [
                'user'     => 'taylor',
                'password' => $password,
                'stat'     => 100,
            ],
            [
                'user'     => 'giovannini',
                'password' => $password,
                'stat'     => 80,
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
     * Get a User model instance with data loaded.
     *
     * This method creates a new User instance, reads its data from the database,
     * and returns the loaded User object.
     *
     * @return User The User model instance with loaded data.
     */
    public function users(): User
    {
        $user = new User($this->pdo, []);
        $user->read();

        return $user;
    }

    /**
     * Assert that every item in the collection is an instance of the User model.
     *
     * @return void
     */
    public function shouldReturnModelEveryItems(): void
    {
        $users = $this->users();

        foreach ($users->get() as $user) {
            $this->assertTrue($user instanceof User);
        }
    }

    /**
     * Test it can get all ids.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetAllIds(): void
    {
        $users = $this->users()->get();

        $this->assertEqualsCanonicalizing(['nuno', 'taylor', 'giovannini'], $users->getPrimaryKey());
    }

    /**
     * Test it can check is clean.
     *
     * @return void
     */
    public function testItCanCheckIsClean(): void
    {
        $users = $this->users();

        $this->assertTrue($users->get()->isclean());
    }

    /**
     * Test it can check is dirty.
     *
     * @return void
     */
    public function testItCanCheckIsDirty(): void
    {
        $users = $this->users();

        $this->assertFalse($users->get()->isDirty());
    }

    /**
     * Test it can read data.
     *
     * @return void
     */
    public function testItCanReadData(): void
    {
        $users = $this->users();

        foreach ($users->get() as $user) {
            $this->assertTrue($user->read());
        }
    }

    /**
     * Test it can upload data.
     *
     * @return void
     */
    public function testItCanUpdateData(): void
    {
        $users = $this->users();

        foreach ($users->get() as $user) {
            $user->setter('stat', 0);
            $this->assertTrue($user->update());
        }
    }

    /**
     * Tets it can delete data.
     *
     * @return void
     */
    public function testItCanDeleteData(): void
    {
        $users = $this->users();

        foreach ($users->get() as $user) {
            $this->assertTrue($user->delete());
        }
    }

    /**
     * Test it can update all with single query.
     *
     * @return void
     */
    public function testItCanUpdateAllWithSingleQuery(): void
    {
        $update = $this->users()->get()->update([
            'stat' => 0,
        ]);

        $this->assertTrue($update);
    }

    /**
     * Test it can delete all with single query.
     *
     * @return void
     */
    public function testItCanDeleteAllWithSingleQuery(): void
    {
        $delete = $this->users()->get()->delete();

        $this->assertTrue($delete);
    }
}
