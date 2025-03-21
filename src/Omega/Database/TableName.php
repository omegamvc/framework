<?php

/**
 * Part of Omega - Model Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database;

/**
 * TableName class.
 *
 * This `TableName` class represents a table name in the Omega CMS package.
 * This class encapsulates the name of a database table.
 *
 * @category   Omega
 * @package    Database
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class TableName
{
    /**
     * TableName class constructor.
     *
     * @param string $name Holds the name of the database table.
     *
     * @return void
     */
    public function __construct(
        public string $name
    ) {
    }
}
