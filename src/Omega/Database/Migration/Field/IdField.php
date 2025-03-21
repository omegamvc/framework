<?php

/**
 * Part of Omega - Database Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0 */

declare(strict_types=1);

namespace Omega\Database\Migration\Field;

use Omega\Database\Exception\MigrationException;

/**
 * ID field class.
 *
 * The `IdField` represents a string field for database migrations.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Migration\Field
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class IdField extends AbstractField
{
    /**
     * Set the default value for int field.
     *
     * @param int $value Holds the default value for the id field.
     * @return $this Returns the current instance for method chaining.
     * @noinspection PhpUnusedParameterInspection
     */
    public function default(int $value): mixed
    {
        throw new MigrationException(
            'ID fields cannot have a default value'
        );
    }
}
