<?php

/**
 * Part of Omega CMS - Database Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Database\Migration;

/*
 * @ue
 */
use Omega\Database\Migration\Field\AbstractField;

/**
 * Migration interface.
 *
 * The `MigrationInterface` defines methods for specifying fields and
 * executing migrations on a database.
 *
 * @category    Omega
 * @package     Database
 * @subpackage  Migration
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
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
     *
     * @return string Return the string for the field.
     */
    public function stringForField(AbstractField $field): string;

    /**
     * Drop column.
     *
     * @param string $name Holds the column name.
     *
     * @return $this
     */
    public function dropColumn(string $name): static;
}
