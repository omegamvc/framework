<?php

declare(strict_types=1);

namespace Omega\Database\Query\Join;

class InnerJoin extends AbstractJoin
{
    /**
     * Create inner join table query.
     *
     * @return string
     */
    protected function joinBuilder(): string
    {
        $on = $this->splitJoin();

        return "INNER JOIN {$this->getAlias()} ON {$on}";
    }
}
