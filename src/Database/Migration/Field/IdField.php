<?php

/**
 * Part of Omega - Database Package.
 *
 * @see       https://omegamvc.github.io
 *
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Database\Migration\Field;

/*
 * @use
 */
use Omega\Database\Exception\MigrationException;

/**
 * ID field class.
 *
 * The `IdField` represents a string field for database migrations.
 *
 * @category    Omega
 * @package     Database
 * @subpackage  Migration\Field
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class IdField extends AbstractField
{
    /**
     * Set the default value for int field.
     *
     * @param int $value Holds the default value for the id field.
     *
     * @return $this Returns the current instance for method chaining.
     */
    public function default(int $value): mixed
    {
        throw new MigrationException(
            'ID fields cannot have a default value'
        );
    }
}
