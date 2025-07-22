<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

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

namespace Omega\Database\Query;

use function array_chunk;
use function count;
use function implode;

/**
 * REPLACE INTO query builder.
 *
 * This class is a specialized version of the `Insert` class that builds a `REPLACE INTO` SQL query.
 * It supports binding values and generating multi-row insertions using REPLACE semantics.
 *
 * The `REPLACE` statement first attempts to insert a new row into a table. If a row with the same
 * unique or primary key already exists, it will be deleted and replaced with the new row.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Query
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Replace extends Insert
{
    /**
     * Build the final REPLACE INTO SQL query.
     *
     * This method assembles the query string based on the current bind values and column names.
     * It supports multiple value sets and will return a full REPLACE INTO statement.
     *
     * @return string The final REPLACE INTO query
     */
    protected function builder(): string
    {
        [$binds, , $columns] = $this->bindsDestructor();

        $stringsBinds = [];
        /** @var array<int, array<int, string>> $chunk */
        $chunk = array_chunk($binds, count($columns), true);
        foreach ($chunk as $group) {
            $stringsBinds[] = '(' . implode(', ', $group) . ')';
        }

        $stringBinds  = implode(', ', $stringsBinds);
        $stringColumn = implode(', ', $columns);

        return $this->query = "REPLACE INTO {$this->table} ({$stringColumn}) VALUES {$stringBinds}";
    }
}
