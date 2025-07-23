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

/**
 * Unit tests for the Profile model using custom query logic.
 *
 * This class sets up a test environment for evaluating the behavior of the Profile model
 * with custom filters, limits, and sorting in a database context.
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
class CustomModelTest extends AbstractDatabase
{
    /**
     * Predefined set of user profiles to be inserted into the test database.
     *
     * @var array<string, array{user: string, name: string, gender: string, age: int}>
     */
    private array $profiles = [
        'taylor'     => [
            'user'   => 'taylor',
            'name'   => 'taylor otwell',
            'gender' => 'male',
            'age'    => 45,
        ],
        'nuno'       => [
            'user'   => 'nuno',
            'name'   => 'nuno maduro',
            'gender' => 'male',
            'age'    => 40,
        ],
        'elena'      => [
            'user'   => 'elena',
            'name'   => 'elena w',
            'gender' => 'female',
            'age'    => 38,
        ],
        'giovannini' => [
            'user'   => 'giovannini',
            'name'   => 'adriano giovannini',
            'gender' => 'male',
            'age'    => 54,
        ],
    ];

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
        $this->createProfileSchema();
        $this->createProfiles($this->profiles);
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
     * Create the schema for the profiles table in the test database.
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
                age       int(3) NOT NULL,
                PRIMARY KEY (user)
            )')
            ->execute();
    }

    /**
     * Insert multiple profile records into the test database.
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
     * Get a new instance of the Profile model for testing.
     *
     * @return Profile
     */
    private function profiles(): Profile
    {
        return new Profile($this->pdo, []);
    }

    /**
     * Test it can filter model.
     *
     * @return void
     */
    public function testItCanFilterModel(): void
    {
        $profiles = $this->profiles();
        $profiles->filterGender('male');
        $profiles->read();

        foreach ($profiles->get() as $profile) {
            $this->assertEquals('male', $profile->getter('gender'));
        }
    }

    /**
     * Test it can filter model chain.
     *
     * @return void
     */
    public function testItCanFilterModelChain(): void
    {
        $profiles = $this->profiles();
        $profiles->filterGender('male');
        $profiles->filterAge(30);
        $profiles->read();

        foreach ($profiles->get() as $profile) {
            $this->assertEquals('male', $profile->getter('gender'));
            $this->assertGreaterThan(30, $profile->getter('gender'));
        }
    }

    /**
     * Test it can limit order.
     *
     * @return void
     */
    public function testItCanLimitOrder(): void
    {
        $profiles = $this->profiles();
        $profiles->limitEnd(2);
        $profiles->read();

        $this->assertEquals(2, $profiles->get()->count());
    }

    /**
     * Test it can limit offset.
     *
     * @return void
     */
    public function testItCanLimitOffset(): void
    {
        $profiles = $this->profiles();
        $profiles->limitOffset(1, 2);
        $profiles->read();

        $this->assertEquals(1, $profiles->get()->count());
    }

    /**
     * Test it can short order.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanShortOrder(): void
    {
        $profiles = $this->profiles();

        $profiles->order('user');
        $profiles->read();
        $this->assertEquals([
            'user'   => 'elena',
            'name'   => 'elena w',
            'gender' => 'female',
            'age'    => 38,
        ], $profiles->first());
    }
}
