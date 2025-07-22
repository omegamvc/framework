<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Database\Schema\Create;
use Omega\Database\Schema\Drop;
use Omega\Database\Schema\Table\Alter;
use Omega\Database\Schema\Table\Create as TableCreate;
use Omega\Database\Schema\Table\Raw;
use Omega\Database\Schema\Table\Truncate;

/**
 * Facade for database schema operations.
 *
 * This facade provides a static interface for manipulating the database structure at runtime.
 * It allows creating, modifying, dropping, and refreshing tables, as well as executing raw SQL statements.
 *
 * Useful for migrations, installation scripts, or dynamic schema changes during tests or setup.
 *
 * Example:
 * ```php
 * Schema::create()->table('users', fn (Blueprint $table) => $table->id()->string('email'));
 * Schema::alter('users', fn (Blueprint $table) => $table->dropColumn('age'));
 * Schema::drop()->table('old_logs');
 * ```
 * @category   Omega
 * @package    Support
 * @subpackage Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 *
 * @method static Create create() Start a new create schema operation.
 * @method static Drop drop() Start a new drop schema operation.
 * @method static Truncate refresh(string $tableName) Truncate (refresh) the given table.
 * @method static TableCreate table(string $tableName, callable $blueprint) Define and create a new table with a blueprint.
 * @method static Alter alter(string $tableName, callable $blueprint) Alter an existing table using a blueprint.
 * @method static Raw raw(string $raw) Execute a raw SQL schema statement.
 */
class Schema extends Facade
{
    /**
     * Get the service accessor key for the schema builder.
     *
     * This key is used by the facade base class to resolve the schema manager instance
     * from the application container.
     *
     * @return string The schema service accessor key.
     */
    protected static function getAccessor(): string
    {
        return 'Schema';
    }
}
