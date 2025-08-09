<?php

declare(strict_types=1);

namespace Omega\Database\MyQuery\Join;

class FullJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        $on = $this->splitJoin();

        return "FULL OUTER JOIN {$this->getAlias()} ON {$on}";
    }
}
