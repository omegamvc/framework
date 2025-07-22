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

use Omega\Database\Query\Table;

/**
 * Facade for the database query builder.
 *
 * Provides static access to the application's query builder layer,
 * allowing the creation and execution of fluent SQL queries via the Query engine.
 *
 * This facade is typically used for interacting directly with database tables
 * in a chainable and expressive way.
 *
 * Example:
 * ```php
 * DB::table('users')->where('active', true)->get();
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
 * @method static Table table(string $from) Start a query on the given table.
 */
class DB extends Facade
{
    /**
     * Get the service accessor key for the database query service.
     *
     * This key is used by the base Facade class to resolve the Query instance
     * from the application container.
     *
     * @return string The query builder service accessor key.
     */
    protected static function getAccessor(): string
    {
        return 'Query';
    }
}
