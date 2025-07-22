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

namespace Omega\Database\Query\Traits;

use Omega\Database\Query\Bind;
use Omega\Database\Query\Delete;
use Omega\Database\Query\Select;
use Omega\Database\Query\Update;
use Omega\Database\Query\Where;

use function str_replace;

/**
 * Provides reusable methods for building SQL WHERE conditions
 * in query builder classes such as Select, Update, Delete, and Where.
 *
 * This trait supports standard condition types such as `equal`, `like`, `in`, `between`,
 * and allows combining them with custom logic.
 *
 * Intended to be used inside query-related classes that have:
 * - a `$where` array property for condition strings,
 * - a `$binds` array for bind values (Bind instances),
 * - a `$filters` array for internal comparison tracking,
 * - a `$strictMode` boolean to toggle between AND/OR concatenation,
 * - and optionally a `$subQuery` and `$table` for alias resolution.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Query\Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
trait ConditionTrait
{
    /**
     * Add an `=` comparison to the WHERE clause.
     *
     * @param string               $bind  Column name or expression.
     * @param bool|int|string|null $value Value to compare against.
     * @return static
     */
    public function equal(string $bind, bool|int|string|null $value): static
    {
        $this->compare($bind, '=', $value);

        return $this;
    }

    /**
     * Add a `LIKE` comparison to the WHERE clause.
     *
     * @param string               $bind  Column name or expression.
     * @param bool|int|string|null $value Value to compare against.
     * @return static
     */
    public function like(string $bind, bool|int|string|null $value): static
    {
        $this->compare($bind, 'LIKE', $value);

        return $this;
    }

    /**
     * Add a custom condition to the WHERE clause.
     *
     * @param string                 $whereCondition Raw SQL condition (e.g. `a = :a`).
     * @param array<int, array|Bind> $binder         Bindings for the condition, as [key, value] or Bind instances.
     * @return ConditionTrait|Delete|Select|Update|Where
     */
    public function where(string $whereCondition, array $binder = []): self
    {
        $this->where[] = $whereCondition;

        foreach ($binder as $bind) {
            if ($bind instanceof Bind) {
                $this->binds[] = $bind;
                continue;
            }
            $this->binds[] = Bind::set($bind[0], $bind[1])->prefixBind('');
        }

        return $this;
    }

    /**
     * Add a `BETWEEN` condition to the WHERE clause.
     *
     * @param string $columnName Column to apply the BETWEEN on.
     * @param int    $value_1    Start value.
     * @param int    $value_2    End value.
     * @return ConditionTrait|Delete|Select|Update|Where
     */
    public function between(string $columnName, int $value_1, int $value_2): self
    {
        $tableName = null === $this->subQuery ? $this->table : $this->subQuery->getAlias();

        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        $this->where(
            "({$tableName}.{$columnName} BETWEEN :b_start AND :b_end)"
        );

        $this->binds[] = Bind::set('b_start', $value_1);
        $this->binds[] = Bind::set('b_end', $value_2);

        return $this;
    }

    /**
     * Add an `IN (...)` condition to the WHERE clause.
     *
     * @param string                                  $columnName Column to check.
     * @param array<int|string, string|int|bool|null> $value      Values to include.
     * @return ConditionTrait|Delete|Select|Update|Where
     */
    public function in(string $columnName, array $value): self
    {
        $binds  = [];
        $binder = [];
        foreach ($value as $key => $bind) {
            $binds[]  = ":in_$key";
            $binder[] = [":in_$key", $bind];
        }
        $bindString = implode(', ', $binds);
        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        $tableName  = null === $this->subQuery ? "{$this->table}" : $this->subQuery->getAlias();

        /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
        $this->where(
            "({$tableName}.{$columnName} IN ({$bindString}))",
            $binder
        );

        return $this;
    }

    /**
     * Add a comparison condition with a custom operator.
     *
     * @param string               $bind       Column or expression to compare.
     * @param string               $comparison Comparison operator (e.g., =, >, <, LIKE, etc.).
     * @param bool|int|string|null $value      Value to compare.
     * @param bool                 $bindValue  Whether to force binding the value.
     * @return ConditionTrait|Delete|Select|Update|Where
     */
    public function compare(
        string $bind,
        string $comparison,
        bool|int|string|null $value,
        bool $bindValue = false
    ): self {
        $escapeBind           = str_replace('.', '__', $bind);
        $this->binds[]        = Bind::set($escapeBind, $value);
        $this->filters[$bind] = [
            'value'      => $value,
            'comparison' => $comparison,
            'bind'       => $escapeBind,
            $bindValue,
        ];

        return $this;
    }

    /**
     * Toggle strict mode for condition concatenation.
     *
     * @param bool $strict True for `AND`, false for `OR`.
     * @return ConditionTrait|Delete|Select|Update|Where
     */
    public function strictMode(bool $strict): self
    {
        $this->strictMode = $strict;

        return $this;
    }
}
