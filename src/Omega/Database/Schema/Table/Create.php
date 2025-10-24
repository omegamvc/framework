<?php

declare(strict_types=1);

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\Query;
use Omega\Database\Schema\Table\Attributes\DataType;
use Omega\Database\Schema\SchemaConnectionInterface;

use function array_map;
use function array_merge;
use function count;
use function implode;

class Create extends Query
{
    /** @var string  */
    public const string INNODB    = 'INNODB';

    /** @var string  */
    public const string MYISAM    = 'MYISAM';

    /** @var string  */
    public const string MEMORY    = 'MEMORY';

    /** @var string  */
    public const string MERGE     = 'MERGE';

    /** @var string  */
    public const string EXAMPLE   = 'EXAMPLE';

    /** @var string  */
    public const string ARCHIVE   = 'ARCHIVE';

    /** @var string  */
    public const string CSV       = 'CSV';

    /** @var string  */
    public const string BLACKHOLE = 'BLACKHOLE';

    /** @var string  */
    public const string FEDERATED = 'FEDERATED';

    /** @var Column[]|DataType[] */
    private array $columns;

    /** @var string[] */
    private array $primaryKeys;

    /** @var string[] */
    private array $uniques;

    /** @var string */
    private string $storeEngine;

    private string $characterSet;

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
        $this->pdo          = $pdo;
        $this->columns      = [];
        $this->primaryKeys  = [];
        $this->uniques      = [];
        $this->storeEngine  = '';
        $this->characterSet = '';
    }

    /**
     * @param string $columnName
     * @return DataType
     */
    public function __invoke(string $columnName): DataType
    {
        return $this->columns[] = new Column()->column($columnName);
    }

    /**
     * @return Column
     */
    public function addColumn(): Column
    {
        return $this->columns[] = new Column();
    }

    /**
     * @param Column[] $columns
     * @return $this
     */
    public function columns(array $columns): self
    {
        $this->columns = [];
        foreach ($columns as $column) {
            $this->columns[] = $column;
        }

        return $this;
    }

    /**
     * @param string $columnName
     * @return $this
     */
    public function primaryKey(string $columnName): self
    {
        $this->primaryKeys[] = $columnName;

        return $this;
    }

    /**
     * @param string $unique
     * @return $this
     */
    public function unique(string $unique): self
    {
        $this->uniques[] = $unique;

        return $this;
    }

    /**
     * @param string $engine
     * @return $this
     */
    public function engine(string $engine): self
    {
        $this->storeEngine = $engine;

        return $this;
    }

    /**
     * @param string $characterSet
     * @return $this
     */
    public function character(string $characterSet): self
    {
        $this->characterSet = $characterSet;

        return $this;
    }

    /**
     * @return string
     */
    protected function builder(): string
    {
        $columns = array_merge($this->getColumns(), $this->getPrimaryKey(), $this->getUnique());
        $columns = $this->join($columns, ', ');
        $query   = $this->join([
            $this->tableName, '(', $columns, ')' . $this->getStoreEngine() . $this->getCharacterSet()
        ]);

        return 'CREATE TABLE ' . $query;
    }

    /**
     * @return string[]
     */
    private function getColumns(): array
    {
        $res = [];

        foreach ($this->columns as $attribute) {
            $res[] = $attribute->__toString();
        }

        return $res;
    }

    /**
     * @return string[]
     */
    private function getPrimaryKey(): array
    {
        if (count($this->primaryKeys) === 0) {
            return [''];
        }

        $primaryKeys = array_map(fn ($primaryKey) => $primaryKey, $this->primaryKeys);
        $primaryKeys = implode(', ', $primaryKeys);

        return ["PRIMARY KEY ($primaryKeys)"];
    }

    /**
     * @return string[]
     */
    private function getUnique(): array
    {
        if (count($this->uniques) === 0) {
            return [''];
        }

        $uniques = implode(', ', $this->uniques);

        return ["UNIQUE ($uniques)"];
    }

    /**
     * @return string
     */
    private function getStoreEngine(): string
    {
        return $this->storeEngine === '' ? '' : ' ENGINE=' . $this->storeEngine;
    }

    /**
     * @return string
     */
    private function getCharacterSet(): string
    {
        return $this->characterSet === '' ? '' : " CHARACTER SET " . $this->characterSet;
    }
}
