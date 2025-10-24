<?php

declare(strict_types=1);

namespace Omega\Database\Query\Join;

use Omega\Database\Query\InnerQuery;

use function implode;

abstract class AbstractJoin
{
    /** @var string */
    protected string $mainTable = '';

    /** @var string */
    protected string $tableName = '';

    /** @var string */
    protected string $columnName = '';

    /** @var string[] */
    protected array $compareColumn = [];

    /** @var string */
    protected string $stringJoin    = '';

    /** @var InnerQuery|null  */
    protected ?InnerQuery $subQuery = null;

    final public function __construct()
    {
    }

    /**
     * @param string $mainTable
     * @return self
     */
    public function __invoke(string $mainTable): self
    {
        $this->mainTable = $mainTable;

        return $this;
    }

    public function __toString(): string
    {
        return $this->stringJoin();
    }

    /**
     * Instance of class.
     *
     * @param string|InnerQuery $refTable Name of the table want to join or sub query
     * @param string            $id       Main id of the table
     * @param string|null       $refId    The id of the table want to join, null means same as main id
     * @return AbstractJoin
     */
    public static function ref(InnerQuery|string $refTable, string $id, ?string $refId = null): AbstractJoin
    {
        $instance = new static();

        if ($refTable instanceof InnerQuery) {
            return $instance
                ->clause($refTable)
                ->compare($id, $refId);
        }

        return $instance
            ->tableRef($refTable)
            ->compare($id, $refId);
    }

    // setter

    /**
     * set main table / master table.
     *
     * @param string $mainTable Name of the master table
     * @return self
     */
    public function table(string $mainTable): self
    {
        $this->mainTable = $mainTable;

        return $this;
    }

    /**
     * @param InnerQuery $select
     * @return $this
     */
    public function clause(InnerQuery $select): self
    {
        $this->subQuery  = $select;
        $this->tableName = $select->getAlias();

        return $this;
    }

    /**
     * Set table reference.
     *
     * @param string $refTable Name of the ref table
     * @return self
     */
    public function tableRef(string $refTable): self
    {
        $this->tableName = $refTable;

        return $this;
    }

    /**
     * set main table and ref table.
     *
     * @param string $mainTable Name of the master table
     * @param string $refTable  Name of the ref table
     * @return self
     */
    public function tableRelation(string $mainTable, string $refTable): self
    {
        $this->mainTable = $mainTable;
        $this->tableName = $refTable;

        return $this;
    }

    /**
     * Compare identical two table.
     *
     * @param string      $mainColumn    Identical of the main table column
     * @param string|null $compareColumn Identical of the ref table column
     * @return self
     */
    public function compare(string $mainColumn, ?string $compareColumn = null): self
    {
        $compareColumn ??= $mainColumn;

        $this->compareColumn[] = [
            $mainColumn, $compareColumn,
        ];

        return $this;
    }

    // getter
    /**
     * Get string of raw join builder.
     *
     * @return string String of raw join builder
     */
    public function stringJoin(): string
    {
        return $this->joinBuilder();
    }

    // main

    /**
     * Setup builder.
     *
     * @return string Raw of builder join
     */
    protected function joinBuilder(): string
    {
        return $this->stringJoin;
    }

    /**
     * Get string of compare join
     * (ex: a.b = c.d).
     *
     * @return string
     */
    protected function splitJoin(): string
    {
        $on = [];
        foreach ($this->compareColumn as $column) {
            $masterColumn  = $column[0];
            $compareColumn = $column[1];

            $on[] = "$this->mainTable.$masterColumn = $this->tableName.$compareColumn";
        }

        return implode(' AND ', $on);
    }

    /**
     * @return string
     */
    protected function getAlias(): string
    {
        return null === $this->subQuery ? $this->tableName : (string) $this->subQuery;
    }
}
