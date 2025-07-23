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
use Omega\Database\Model\Model;
use Omega\Database\Query\Query;
use Omega\Database\Query\Insert;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;

use function password_hash;

use const PASSWORD_DEFAULT;

/**
 * This test class verifies the behavior of the User model
 * in a multi-user database context. It ensures correct functionality
 * for reading, updating, deleting, relationship handling,
 * data transformation, and model utility methods.
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
#[CoversClass(Model::class)]
#[CoversClass(Query::class)]
#[CoversClass(Insert::class)]
class BaseMultiModelTest extends AbstractDatabase
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
     * Get a User model instance for the user 'taylor'.
     * Optionally reads the user data from the database.
     *
     * @param bool $read
     * @return User
     */
    public function users(bool $read = true): User
    {
        $user = new User($this->pdo, []);
        $user->identifier()->equal('user', 'taylor');
        if ($read) {
            $user->read();
        }

        return $user;
    }

    /**
     * Create the schema for the "profiles" table used in relational tests.
     *
     * @return void
     */
    private function createProfileSchema(): void
    {
        $this
            ->pdo
            ->query('CREATE TABLE profiles (
                user      varchar(32)  NOT NULL,
                name      varchar(100) NOT NULL,
                gender    varchar(10) NOT NULL,
                PRIMARY KEY (user)
            )')
            ->execute();
    }

    /**
     * Insert sample profile data into the "profiles" table.
     *
     * @param array $profiles
     * @return void
     */
    private function createProfiles(array $profiles): void
    {
        (new Insert('profiles', $this->pdo))
            ->rows($profiles)
            ->execute();
    }

    /**
     * Create the schema for the "orders" table used in relational tests.
     *
     * @return void
     */
    private function createOrderSchema(): void
    {
        $this
            ->pdo
            ->query('CREATE TABLE orders (
                id   varchar(3)  NOT NULL,
                user varchar(32)  NOT NULL,
                name varchar(100) NOT NULL,
                type varchar(30) NOT NULL,
                PRIMARY KEY (id)
            )')
            ->execute();
    }

    /**
     * Insert sample order data into the "orders" table.
     *
     * @param array $orders
     * @return void
     */
    private function createOrders(array $orders): void
    {
        (new Insert('orders', $this->pdo))
            ->rows($orders)
            ->execute();
    }

    /**
     * Test it can read data.
     *
     * @return void
     */
    public function testItCanReadData(): void
    {
        $user = new User($this->pdo, [[]], ['user' => ['taylor']]);

        $this->assertTrue($user->read());
    }

    /**
     * Test it can update data.
     *
     * @return void
     */
    public function testItCanUpdateData(): void
    {
        $user = $this->users();

        $user->setter('stat', 75);

        $this->assertTrue($user->update());
    }

    /**
     * Test it can delete data.
     *
     * @return void
     */
    public function testItCanDeleteData(): void
    {
        $user = $this->users();
        $this->assertTrue($user->delete());
    }

    /**
     * Test it can get first.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetFirst(): void
    {
        $users = $this->users();

        $this->assertEquals([
            'user' => 'taylor',
            'stat' => 100,
        ], $users->first());
    }

    /**
     * Test it can get has one.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetHasOne(): void
    {
        // profile
        $profile = [
            'user'   => 'taylor',
            'name'   => 'taylor otwell',
            'gender' => 'male',
        ];
        $this->createProfileSchema();
        $this->createProfiles([$profile]);

        $user   = $this->users();

        $this->assertEquals($profile, $user->profile()->first());
    }

    /**
     * Test it can get has one using magic getter.
     *
     * @return void
     */
    public function testItCanGetHasOneUsingMagicGetter(): void
    {
        // profile
        $profile = [
            'user'   => 'taylor',
            'name'   => 'taylor otwell',
            'gender' => 'male',
        ];
        $this->createProfileSchema();
        $this->createProfiles([$profile]);

        $user   = $this->users();
        $this->assertEquals($profile, $user->profile);
    }

    /**
     * Test it can get has many.
     *
     * @return void
     */
    public function testItCanGetHasMany(): void
    {
        // order
        $order = [
            [
                'id'     => '1',
                'user'   => 'taylor',
                'name'   => 'order 1',
                'type'   => 'gadget',
            ], [
                'id'     => '3',
                'user'   => 'taylor',
                'name'   => 'order 2',
                'type'   => 'gadget',
            ],
        ];
        $this->createOrderSchema();
        $this->createOrders($order);

        $user   = $this->users();
        $result = $user->hasMany(Order::class, 'user');
        $this->assertEquals($order, $result->toArrayArray());
    }

    /**
     * Test it can check clean
     *
     * @return void
     * @throws Exception
     */
    public function testItCanCheckClean(): void
    {
        $user = $this->users();
        $this->assertTrue($user->isClean(), 'Check all column');
        $this->assertTrue($user->isClean('stat'), 'Check specific column');
    }

    /**
     * Test it can check is dirty.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanCheckIsDirty(): void
    {
        $user = $this->users();
        $user->setter('stat', 75);
        $this->assertTrue($user->isDirty(), 'Check all column');
        $this->assertTrue($user->isDirty('stat'), 'Check specific column');
    }

    /**
     * Test it can get change column.
     *
     * @return void
     */
    public function testItCanGetChangeColumn(): void
    {
        $user = $this->users();
        $this->assertEquals([], $user->changes(), 'original fresh data');
        // modify
        $user->setter('stat', 75);
        $this->assertEquals([
            'stat' => 75,
        ], $user->changes(), 'change first column');
    }

    /**
     * Test it can hide column.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanHideColumn(): void
    {
        $user = $this->users();

        $this->assertArrayNotHasKey('password', $user->first(), 'password must hidden by stash');
    }

    /**
     * Test it can convert to array.
     *
     * @return void
     */
    public function testItCanConvertToArray(): void
    {
        $user = $this->users();

        $this->assertEquals([
            [
                'user' => 'taylor',
                'stat' => 100,
            ],
        ], $user->toArray());
        $this->assertIsIterable($user);
    }

    /**
     * Test it can get using getter n column.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetUsingGetterInColumn(): void
    {
        $user = $this->users();

        $columns = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals($columns[0]['stat'], $user->getter('stat', 0));
    }

    /**
     * Test it can set using setter in column.
     *
     * @return void
     */
    public function testItCanSetUsingSetterInColumn(): void
    {
        $user = $this->users();

        $user->setter('stat', 80);
        $columns = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals(80, $columns[0]['stat']);
    }

    /**
     * Test it can check exist.
     *
     * @return void
     */
    public function testItCanCheckExist(): void
    {
        $user = $this->users();

        $this->assertTrue($user->has('user'));
    }

    /**
     * Test it can get using magic setter in column.
     *
     * @return void
     */
    public function testItCanGetUsingMagicGetterInColumn(): void
    {
        $user = $this->users();

        $columns = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals($columns[0]['stat'], $user->stat);
    }

    /**
     * Test it can set using magic setter in column.
     *
     * @return void
     */
    public function testItCanSetUsingMagicSetterInColumn(): void
    {
        $user = $this->users();

        $user->stat = 80;
        $columns    = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals(80, $columns[0]['stat']);
    }

    /**
     * Test it can get using array.
     *
     * @return void
     */
    public function testItCanGetUsingArray(): void
    {
        $user = $this->users();

        $columns = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals($columns[0]['stat'], $user['stat']);
    }

    /**
     * Test it can set using array.
     *
     * @return void
     */
    public function testItCanSetUsingArray(): void
    {
        $user = $this->users();

        $user['stat'] = 80;
        $columns      = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals(80, $columns[0]['stat']);
    }

    /**
     * Test it can check using magic isset.
     *
     * @return void
     */
    public function testItCanCheckUsingMagicIsset(): void
    {
        $user = $this->users();
        $this->assertTrue(isset($user['user']));
    }

    /**
     * Test it can unset using array.
     *
     * @return void
     */
    public function testItCanUnsetUsingArray(): void
    {
        $user = $this->users();

        unset($user['stat']);
        $columns = (fn () => $this->{'columns'})->call($user);
        $this->assertEquals(100, $columns[0]['stat']);
    }

    /**
     * Test it can get collection.
     *
     * @return void
     */
    public function testItCanGetCollection(): void
    {
        $user = $this->users();

        $columns = (fn () => $this->{'columns'})->call($user);
        $models  = $user->get()->toArray();

        $arr = [];
        foreach ($models as $new) {
            $arr[] = (fn () => $this->{'columns'})->call($new)[0];
        }
        $this->assertEquals($columns, $arr);
    }

    // find user by some condition (static)

    /**
     * Test it can find using id.
     *
     * @return void
     */
    public function testItCanFindUsingId(): void
    {
        $user = User::find('taylor', $this->pdo);

        $this->assertTrue($user->has('user'));
    }

    /**
     * Test it can find using where.
     *
     * @return void
     */
    public function testItCanFindUsingWhere(): void
    {
        $user = User::where('user = :user', [
            'user' => 'taylor',
        ], $this->pdo);

        $this->assertTrue($user->has('user'));
    }

    /**
     * Test it can find using equal.
     *
     * @return void
     */
    public function testItCanFindUsingEqual(): void
    {
        $user = User::equal('user', 'taylor', $this->pdo);

        $this->assertTrue($user->has('user'));
    }

    /**
     * Test it can find all.
     *
     * @return void
     */
    public function testItCanFindAll(): void
    {
        $users   = Query::from('users', $this->pdo)->select()->get()->toArray();
        $models  = User::all($this->pdo);

        $map = array_map(fn (Model $model) => $model->toArray()[0], $models->toArray());

        foreach ($users as $key => $user) {
            $this->assertEquals($user['user'], $map[$key]['user']);
        }
    }

    /**
     * Test it can find or create.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanFindOrCreate(): void
    {
        $user = User::findOrCreate('taylor', [
            'user'     => 'taylor',
            'password' => 'password',
            'stat'     => 100,
        ], $this->pdo);

        $this->assertTrue($user->isExist());
        $this->assertEquals('taylor', $user->getter('user', 'nuno'));
    }

    /**
     * Test it can find or create but not exists.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanFindOrCreateButNotExits(): void
    {
        $user = User::findOrCreate('giovannini2', [
            'user'     => 'giovannini2',
            'password' => 'password',
            'stat'     => 100,
        ], $this->pdo);

        $this->assertTrue($user->isExist());
        $this->assertEquals('giovannini2', $user->getter('user', 'giovannini'));
    }
}
