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
use Omega\Database\Query\Insert;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Database\AbstractDatabase;

use function password_hash;

use const PASSWORD_DEFAULT;

/**
 * This test class covers the complete functionality of the User model,
 * including basic CRUD operations, relationship handling (hasOne, hasMany),
 * data transformation (array access, magic access), column manipulation,
 * and static finders (find, where, equal, all, etc.).
 *
 * It also ensures that the model behaves correctly with respect to
 * data cleanliness (isDirty, isClean), hidden attributes, column changes,
 * and primary key retrieval.
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
#[CoversClass(Insert::class)]
class BaseModelTest extends AbstractDatabase
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
        $this->createUser([
            [
                'user'     => 'taylor',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'stat'     => 100,
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
     * Create a new instance of the User model and optionally load its data.
     *
     * This helper method initializes a User object with a predefined identifier
     * for the user named "taylor". If $read is true, it immediately attempts
     * to fetch the corresponding record from the database.
     *
     * @param bool $read Whether to immediately read the user's data from the database.
     * @return User The initialized User model instance.
     */
    public function user(bool $read = true): User
    {
        $user = new User($this->pdo, []);
        $user->identifier()->equal('user', 'taylor');
        if ($read) {
            $user->read();
        }

        return $user;
    }

    /**
     * Creates the `profiles` table schema used for hasOne relation tests.
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
     * Inserts profile records into the `profiles` table.
     *
     * @param array $profiles An array of profile records to insert.
     * @return void
     */
    private function createProfiles(array $profiles): void
    {
        (new Insert('profiles', $this->pdo))
            ->rows($profiles)
            ->execute();
    }

    /**
     * Creates the `orders` table schema used for hasMany relation tests.
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
     * Inserts order records into the `orders` table.
     *
     * @param array $orders An array of order records to insert.
     * @return void
     */
    private function createOrders(array $orders): void
    {
        (new Insert('orders', $this->pdo))
            ->rows($orders)
            ->execute();
    }

    /**
     * Test it can create data.
     *
     * @return void
     */
    public function testItCanCreateData(): void
    {
        $user = new User($this->pdo, [
            [
                'user'     => 'nuno',
                'password' => password_hash('password', PASSWORD_DEFAULT),
                'stat'     => 50,
            ],
        ], [[]]);

        $this->assertTrue($user->insert());
    }

    /**
     * Test it can read data.
     *
     * @return void
     */
    public function testItCanReadData(): void
    {
        $user = new User($this->pdo, []);

        $this->assertTrue($user->read());
    }

    /**
     * Test it can update beta.
     *
     * @return void
     */
    public function testItCanUpdateData(): void
    {
        $user = $this->user();

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
        $user = $this->user();
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
        $users = $this->user();

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

        $user   = $this->user();
        $result = $user->hasOne(Profile::class, 'user');
        $this->assertEquals($profile, $result->first());
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

        $user   = $this->user();
        $this->assertEquals($profile, $user->profile);
    }

    /**
     * Test it can get has one with table name.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetHasOneWithTableName(): void
    {
        // profile
        $profile = [
            'user'   => 'taylor',
            'name'   => 'taylor otwell',
            'gender' => 'male',
        ];
        $this->createProfileSchema();
        $this->createProfiles([$profile]);

        $user   = $this->user();
        $result = $user->hasOne('profiles', 'user');
        $this->assertEquals($profile, $result->first());
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

        $user   = $this->user();
        $result = $user->hasMany(Order::class, 'user');
        $this->assertEquals($order, $result->toArrayArray());
    }

    /**
     * Test it can get has many with magic getter.
     *
     * @return void
     */
    public function testItCanGetHasManyWithMagicGetter(): void
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

        $user   = $this->user();
        $this->assertEquals($order, $user->orders);
    }

    /**
     * Test it can get has many with table name.
     *
     * @return void
     */
    public function testItCanGetHasManyWithTableName(): void
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

        $user   = $this->user();
        $result = $user->hasMany(Order::class, 'user');
        $this->assertEquals($order, $result->toArrayArray());
    }

    /**
     * Test it can checks clen with.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanChecksCleanWith(): void
    {
        $user = $this->user();
        $this->assertTrue($user->isClean(), 'Check all column');
        $this->assertTrue($user->isClean('stat'), 'Check specific column');
    }

    /**
     * Test it can check dirty,
     *
     * @return void
     */
    public function testItCanCheckDirty(): void
    {
        $user = $this->user();
        $user->setter('stat', 75);
        $this->assertTrue($user->isDirty(), 'Check all column');
        $this->assertTrue($user->isDirty('stat'), 'Check specific column');
    }

    /**
     * Test it can check column is existing.
     *
     * @return void
     */
    public function testItCanCheckColumnIsExisting(): void
    {
        $user = $this->user();

        $this->assertTrue($user->isExist());
    }

    /**
     * Test it can get change column.
     *
     * @return void
     */
    public function testItCanGetChangeColumn(): void
    {
        $user = $this->user();
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
        $user = $this->user();

        $this->assertArrayNotHasKey('password', $user->first(), 'password must hidden by stash');
    }

    /**
     * Test it can convert to array.
     *
     * @return void
     */
    public function testItCanConvertToArray(): void
    {
        $user = $this->user();

        $this->assertEquals([
            [
                'user' => 'taylor',
                'stat' => 100,
            ],
        ], $user->toArray());
        $this->assertIsIterable($user);
    }

    /**
     * Test it can get first primary key.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetFirstPrimaryKey(): void
    {
        $user = $this->user();

        $this->assertEquals('taylor', $user->getPrimaryKey());
    }

    /**
     * Test it can get using getter in column.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanGetUsingGetterInColumn(): void
    {
        $user = $this->user();

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
        $user = $this->user();

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
        $user = $this->user();

        $this->assertTrue($user->has('user'));
    }

    /**
     * Test it can get using magic getter in column.
     *
     * @return void
     */
    public function testItCanGetUsingMagicGetterInColumn(): void
    {
        $user = $this->user();

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
        $user = $this->user();

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
        $user = $this->user();

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
        $user = $this->user();

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
        $user = $this->user();
        $this->assertTrue(isset($user['user']));
    }

    /**
     * Test it can unset using array.
     *
     * @return void
     */
    public function testItCanUnsetUsingArray(): void
    {
        $user = $this->user();

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
        $user = $this->user();

        $columns = (fn () => $this->{'columns'})->call($user);
        $models  = $user->get()->toArray();

        $arr = [];
        foreach ($models as $new) {
            $arr[] = (fn () => $this->{'columns'})->call($new)[0];
        }
        $this->assertEquals($columns, $arr);
    }

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
     * est it can find all
     *
     * @return void
     */
    public function testItCanFindAll(): void
    {
        $columns = (fn () => $this->{'columns'})->call($this->user());
        $models  = User::all($this->pdo)->toArray();

        $arr = [];
        foreach ($models as $new) {
            $arr[] = (fn () => $this->{'columns'})->call($new)[0];
        }
        $this->assertEquals($columns, $arr);
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
     * Test it can find or crete but not exist.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanFindOrCreateButNotExits(): void
    {
        $user = User::findOrCreate('giovannini', [
            'user'     => 'giovannini',
            'password' => 'password',
            'stat'     => 100,
        ], $this->pdo);

        $this->assertTrue($user->isExist());
        $this->assertEquals('giovannini', $user->getter('user', 'nuno'));
    }
}
