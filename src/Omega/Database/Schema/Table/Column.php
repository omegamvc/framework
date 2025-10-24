<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\Table\Attributes\Alter\DataType as AlterDataType;
use Omega\Database\Schema\Table\Attributes\DataType;

class Column
{
    /** @var string|DataType|AlterDataType */
    protected string|DataType|AlterDataType $query;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->query;
    }

    /**
     * @param string $columnName
     * @return DataType
     */
    public function column(string $columnName): DataType
    {
        return $this->query = new DataType($columnName);
    }

    /**
     * @param string $columnName
     * @return AlterDataType
     */
    public function alterColumn(string $columnName): AlterDataType
    {
        return $this->query = new AlterDataType($columnName);
    }

    /**
     * @param string $query
     * @return $this
     */
    public function raw(string $query): self
    {
        $this->query = $query;

        return $this;
    }
}
