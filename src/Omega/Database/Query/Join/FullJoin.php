<?php

declare(strict_types=1);

namespace Omega\Database\Query\Join;

class FullJoin extends AbstractJoin
{
    /**
     * Create full join table query.
     *
     * @return string
     */
    protected function joinBuilder(): string
    {
        $on = $this->splitJoin();

        return "FULL OUTER JOIN {$this->getAlias()} ON {$on}";
    }
}
