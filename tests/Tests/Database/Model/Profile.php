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

/**
 * Profile model.
 *
 * This model represents the "profiles" table and defines "user" as its primary key.
 * It provides filtering capabilities by gender and age.
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
class Profile extends Model
{
    /** @var string The name of the table associated with the model. */
    protected string $tableName = 'profiles';

    /** @var string The name of the primary key column. */
    protected string $primaryKey = 'user';

    /**
     * Filter profiles by gender.
     *
     * Adds a condition to the query to match the specified gender.
     *
     * @param string $gender The gender to filter by (e.g., 'male', 'female').
     * @return static
     */
    public function filterGender(string $gender): static
    {
        $this->where->equal('gender', $gender);

        return $this;
    }

    /**
     * Filter profiles by minimum age.
     *
     * Adds a condition to the query to only include profiles where the age is greater than the given value.
     *
     * @param int $greaterThan The minimum age threshold.
     * @return static
     */
    public function filterAge(int $greaterThan): static
    {
        $this->where->compare('age', '>', $greaterThan);

        return $this;
    }
}
