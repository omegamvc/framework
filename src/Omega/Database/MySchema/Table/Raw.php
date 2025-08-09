<?php

declare(strict_types=1);

namespace Omega\Database\MySchema\Table;

use Omega\Database\MySchema\MyPDO;
use Omega\Database\MySchema\Query;

class Raw extends Query
{
    private string $raw;

    public function __construct(string $raw, MyPDO $pdo)
    {
        $this->raw   = $raw;
        $this->pdo   = $pdo;
    }

    protected function builder(): string
    {
        return $this->raw;
    }
}
