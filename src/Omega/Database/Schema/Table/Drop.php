<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\Query;
use Omega\Database\Schema\Traits\ConditionTrait;
use Omega\Database\Schema\SchemaConnectionInterface;

class Drop extends Query
{
    use ConditionTrait;

    /** @var string */
    private string $tableName;

    /**
     * @param string                    $databaseName
     * @param string                    $tableName
     * @param SchemaConnectionInterface $pdo
     */
    public function __construct(string $databaseName, string $tableName, SchemaConnectionInterface $pdo)
    {
        $this->tableName    = $databaseName . '.' . $tableName;
        $this->pdo           = $pdo;
    }

    /**
     * @return string
     */
    protected function builder(): string
    {
        $condition = $this->join([$this->ifExists, $this->tableName]);

        return 'DROP TABLE ' . $condition . ';';
    }
}
