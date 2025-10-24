<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\Query;
use Omega\Database\Schema\SchemaConnectionInterface;

class Raw extends Query
{
    /** @var string  */
    private string $raw;

    /**
     * @param string                    $raw
     * @param SchemaConnectionInterface $pdo
     */
    public function __construct(string $raw, SchemaConnectionInterface $pdo)
    {
        $this->raw = $raw;
        $this->pdo = $pdo;
    }

    /**
     * @return string
     */
    protected function builder(): string
    {
        return $this->raw;
    }
}
