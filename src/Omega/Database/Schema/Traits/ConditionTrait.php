<?php

/**
 * Part of Omega - Database Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Schema\Traits;

/**
 * Trait ConditionTrait
 *
 * Provides conditional SQL clauses like IF EXISTS and IF NOT EXISTS
 * for use in schema manipulation statements (e.g., CREATE, DROP).
 *
 * This trait is intended to be used by schema builder classes to
 * dynamically add conditional logic to DDL statements.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Schema\Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
trait ConditionTrait
{
    /**
     * SQL condition clause used in statements like
     * CREATE TABLE IF NOT EXISTS or DROP TABLE IF EXISTS.
     *
     * @var string
     */
    private string $ifExists = '';

    /**
     * Enables "IF EXISTS" or "IF NOT EXISTS" in the SQL statement,
     * depending on the given boolean value.
     *
     * @param bool $value If true, sets "IF EXISTS"; otherwise "IF NOT EXISTS"
     * @return self
     */
    public function ifExists(bool $value = true): self
    {
        $this->ifExists = $value
            ? 'IF EXISTS'
            : 'IF NOT EXISTS';

        return $this;
    }

    /**
     * Enables "IF NOT EXISTS" or "IF EXISTS" in the SQL statement,
     * depending on the given boolean value.
     *
     * @param bool $value If true, sets "IF NOT EXISTS"; otherwise "IF EXISTS"
     * @return self
     */
    public function ifNotExists(bool $value = true): self
    {
        $this->ifExists = $value
            ? 'IF NOT EXISTS'
            : 'IF EXISTS';

        return $this;
    }
}
