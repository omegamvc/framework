<?php

/**
 *
 */

/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Database\Query\Traits;

use Omega\Database\Query\Bind;

use function implode;
use function str_replace;

/**
 * Trait to provide condition under class extend with Query::class.
 */
trait ConditionTrait
{
    /**
     * Insert 'equal' condition in (query builder).
     *
     * @param string               $bind  Bind
     * @param bool|int|string|null $value Value of bind
     * @return self
     */
    public function equal(string $bind, bool|int|string|null $value): self
    {
        $this->compare($bind, '=', $value);

        return $this;
    }

    /**
     * Insert 'like' condition in (query builder).
     *
     * @param string               $bind  Bind
     * @param bool|int|string|null $value Value of bind
     * @return self
     */
    public function like(string $bind, bool|int|string|null $value): self
    {
        $this->compare($bind, 'LIKE', $value);

        return $this;
    }

    /**
     * Insert 'where' condition in (query builder).
     *
     * @param string                 $whereCondition Specific column name
     * @param array<int, array|Bind> $binder         Bind and value (use for 'in')
     * @return $this
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
     * Insert 'between' condition in (query builder).
     *
     * @param string $columnName Specific column name
     * @param int    $value_1     Between start
     * @param int    $value_2     Between end
     * @return self
     */
    public function between(string $columnName, int $value_1, int $value_2): self
    {
        $tableName = null === $this->subQuery ? $this->table : $this->subQuery->getAlias();

        $this->where(
            "({$tableName}.{$columnName} BETWEEN :b_start AND :b_end)"
        );

        $this->binds[] = Bind::set('b_start', $value_1);
        $this->binds[] = Bind::set('b_end', $value_2);

        return $this;
    }

    /**
     * Insert 'in' condition (query builder).
     *
     * @param string                                  $columnName Specific column name
     * @param array<int|string, string|int|bool|null> $value      Bind and value (use for 'in')
     * @return self
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
        $tableName = null === $this->subQuery ? "{$this->table}" : $this->subQuery->getAlias();

        $this->where(
            "({$tableName}.{$columnName} IN ({$bindString}))",
            $binder
        );

        return $this;
    }

    /**
     * Where statement setter,
     *
     * @param string               $bind
     * @param string               $comparison
     * @param bool|int|string|null $value
     * @param bool                 $bindValue
     * @return self
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
     * Setter strict mode.
     *
     * True = operator using AND,
     * False = operator using OR
     *
     * @param bool $strict
     * @return self
     */
    public function strictMode(bool $strict): self
    {
        $this->strictMode = $strict;

        return $this;
    }
}
