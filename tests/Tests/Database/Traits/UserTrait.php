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

namespace Tests\Database\Traits;

use Omega\Database\Query\Query;

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

/**
 * Provides helper assertions for testing user-related database records.
 *
 * This trait defines reusable methods to assert the existence of users,
 * their absence, and the correctness of user-related values (e.g. `stat`)
 * in the `users` table. Intended for use in integration or functional tests
 * where user records are part of the setup or verification process.
 *
 * @category   Omega\Tests
 * @package    Database
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
trait UserTrait
{
    /**
     * Asserts that a user with the given username exists in the database.
     *
     * Performs a SELECT query on the `users` table to check if a row
     * with the specified username is present.
     *
     * @param string $user The username to check for.
     * @return void
     */
    protected function assertUserExist(string $user): void
    {
        $data  = Query::from('users', $this->pdo)
            ->select(['user'])
            ->equal('user', $user)
            ->all();

        assertTrue(count($data) === 1, 'expect user exist in database');
    }

    /**
     * Asserts that a user with the given username does not exist in the database.
     *
     * Performs a SELECT query on the `users` table and ensures that
     * no row matches the given username.
     *
     * @param string $user The username expected to be absent.
     * @return void
     */
    protected function assertUserNotExist(string $user): void
    {
        $data  = Query::from('users', $this->pdo)
            ->select(['user'])
            ->equal('user', $user)
            ->all();

        assertTrue(count($data) === 0, 'expect user exist in database');
    }

    /**
     * Asserts that a user's `stat` value matches the expected value.
     *
     * Retrieves the `stat` column for the given user from the `users` table
     * and compares it to the provided expected value.
     *
     * @param string $user   The username to check.
     * @param int    $expect The expected stat value.
     * @return void
     */
    protected function assertUserStat(string $user, int $expect): void
    {
        $data  = Query::from('users', $this->pdo)
            ->select(['stat'])
            ->equal('user', $user)
            ->all();

        assertEquals($expect, (int) $data[0]['stat'], 'expect user stat');
    }
}
