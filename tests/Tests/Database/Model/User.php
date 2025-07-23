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

use Omega\Database\Model\Model;
use Omega\Database\Model\ModelCollection;

/**
 * User model.
 *
 * @property int|array $stat
 * @property Profile|Profile[] $profile
 * @property Order[]|ModelCollection $orders
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
class User extends Model
{
    /** @var string The name of the table associated with the model. */
    protected string $tableName  = 'users';

    /** @var string The name of the primary key column. */
    protected string $primaryKey = 'user';

    /** @var string[] Hide from showing column */
    protected array $stash = ['password'];

    /**
     * Get the user's profile (has-one relationship).
     *
     * @return Profile|User
     */
    public function profile(): User|Profile
    {
        return $this->hasOne(Profile::class, 'user');
    }

    /**
     * Get the user's orders (has-many relationship).
     *
     * @return ModelCollection
     */
    public function orders(): ModelCollection
    {
        return $this->hasMany(Order::class, 'user');
    }
}
