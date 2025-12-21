<?php

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\Query\Traits\ConditionTrait;
use Omega\Database\Query\Traits\SubQueryTrait;

class Where
{
    use ConditionTrait;
    use SubQueryTrait;

    /** @var string Table Name */
    private string $table;

    /** @var InnerQuery|null This property use for helper php-stan (auto skip) */
    private ?InnerQuery $subQuery = null;

    /**  @var Bind[] Binder for PDO bind */
    private array $binds = [];

    /** @var string[] Final where statement. */
    private array $where = [];

    /** @var array<string, string> Single filter and single strict mode. */
    private array $filters = [];

    /** @var bool True if you use AND instance of OR */
    private bool $strictMode = true;

    /**
     * @param string $tableName
     */
    public function __construct(string $tableName)
    {
        $this->table = $tableName;
    }

    /**
     * Get raw property.
     *  - binds
     *  - where
     *  - filter
     *  - isStrict.
     *
     * @return array<string, Bind[]|string[]|array<string, string>|bool>
     */
    public function get(): array
    {
        return [
            'binds'     => $this->binds,
            'where'     => $this->where,
            'filters'   => $this->filters,
            'isStrict'  => $this->strictMode,
        ];
    }

    /**
     * Reset all condition.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->binds      = [];
        $this->where      = [];
        $this->filters    = [];
        $this->strictMode = true;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return [] === $this->binds
            && [] === $this->where
            && [] === $this->filters
            && true === $this->strictMode;
    }
}
