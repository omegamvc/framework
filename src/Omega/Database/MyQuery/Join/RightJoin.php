<?php

declare(strict_types=1);

namespace Omega\Database\MyQuery\Join;

class RightJoin extends AbstractJoin
{
    protected function joinBuilder(): string
    {
        $on = $this->splitJoin();

        return "RIGHT JOIN {$this->getAlias()} ON {$on}";
    }
}
