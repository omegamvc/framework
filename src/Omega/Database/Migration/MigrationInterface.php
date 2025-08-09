<?php

/**
 * Part of Omega - Database Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Migration;

use Omega\Database\Migration\Field\AbstractField;

/**
 * Migration interface.
 *
 * The `MigrationInterface` defines methods for specifying fields and
 * executing migrations on a database.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Migration
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface MigrationInterface
{
    /**
     * Execute migration.
     *
     * @return void
     */
    public function execute(): void;

    /**
     * Rolls back the migration (drops the table).
     *
     * @return void
     */
    public function down(): void;

    /**
     * String for field.
     *
     * @param AbstractField $field Holds an instance of AbstractField.
     * @return string Return the string for the field.
     */
    public function stringForField(AbstractField $field): string;

    /**
     * Drop column.
     *
     * @param string $name Holds the column name.
     * @return $this
     */
    public function dropColumn(string $name): static;
}
