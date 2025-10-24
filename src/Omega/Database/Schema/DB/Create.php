<?php

declare(strict_types=1);

namespace Omega\Database\Schema\DB;

use Omega\Database\Schema\Query;
use Omega\Database\Schema\Traits\ConditionTrait;
use Omega\Database\Schema\SchemaConnectionInterface;

class Create extends Query
{
    use ConditionTrait;

    /** @var string */
    private string $databaseName;

    /**
     * @param string $databaseName
     * @param SchemaConnectionInterface $pdo
     */
    public function __construct(string $databaseName, SchemaConnectionInterface $pdo)
    {
        $this->databaseName = $databaseName;
        $this->pdo          = $pdo;
    }

    protected function builder(): string
    {
        $condition = $this->join([$this->ifExists, $this->databaseName]);

        return 'CREATE DATABASE ' . $condition . ';';
    }
}
