<?php

declare(strict_types=1);

namespace Omega\Database\MySchema\Table;

use Omega\Database\MySchema\Query;
use Omega\Database\MySchema\SchemaConnectionInterface;

class Raw extends Query
{
    private string $raw;

    public function __construct(string $raw, SchemaConnectionInterface $pdo)
    {
        $this->raw   = $raw;
        $this->pdo   = $pdo;
    }

    protected function builder(): string
    {
        return $this->raw;
    }
}
