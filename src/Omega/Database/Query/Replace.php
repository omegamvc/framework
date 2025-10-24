<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Database\Query;

use function array_chunk;
use function count;
use function implode;

class Replace extends Insert
{
    /**
     * @return string
     */
    protected function builder(): string
    {
        [$binds, ,$columns] = $this->bindsDestructor();

        $stringsBinds = [];
        /** @var array<int, array<int, string>> $chunk */
        $chunk         = array_chunk($binds, count($columns), true);
        foreach ($chunk as $group) {
            $stringsBinds[] = '(' . implode(', ', $group) . ')';
        }

        $stringBinds  = implode(', ', $stringsBinds);
        $stringColumn = implode(', ', $columns);

        return $this->query = "REPLACE INTO {$this->table} ({$stringColumn}) VALUES {$stringBinds}";
    }
}
