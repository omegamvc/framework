<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Database\Schema\Table\Attributes\Alter;

use function array_map;
use function implode;

class DataType
{
    /** @var string */
    private string $name;

    /** @var string|Constraint */
    private string|Constraint $datatype;

    /**
     * @param string $columnName
     */
    public function __construct(string $columnName)
    {
        $this->name     = $columnName;
        $this->datatype = '';
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->query();
    }

    /**
     * @return string
     */
    private function query(): string
    {
        return $this->name . ' ' . $this->datatype;
    }

    // number

    /**
     * @param int $length
     * @return Constraint
     */
    public function int(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('int');
        }

        return $this->datatype = new Constraint("int($length)");
    }

    /**
     * @param int $length
     * @return Constraint
     */
    public function tinyint(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('tinyint');
        }

        return $this->datatype = new Constraint("tinyint($length)");
    }

    /**
     * @param int $length
     * @return Constraint
     */
    public function smallint(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('smallint');
        }

        return $this->datatype = new Constraint("smallint($length)");
    }

    /**
     * @param int $length
     * @return Constraint
     */
    public function bigint(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('bigint');
        }

        return $this->datatype = new Constraint("bigint($length)");
    }

    /**
     * @param int $length
     * @return Constraint
     */
    public function float(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('float');
        }

        return $this->datatype = new Constraint("float($length)");
    }

    // date

    /**
     * @param int $length
     * @return Constraint
     */
    public function time(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('time');
        }

        return $this->datatype = new Constraint("time($length)");
    }

    /**
     * @param int $length
     * @return Constraint
     */
    public function timestamp(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('timestamp');
        }

        return $this->datatype = new Constraint("timestamp($length)");
    }

    /**
     * @return Constraint
     */
    public function date(): Constraint
    {
        return $this->datatype = new Constraint('date');
    }

    // text

    /**
     * @param int $length
     * @return Constraint
     */
    public function varchar(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('varchar');
        }

        return $this->datatype = new Constraint("varchar($length)");
    }

    /**
     * @param int $length
     * @return Constraint
     */
    public function text(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('text');
        }

        return $this->datatype = new Constraint("text($length)");
    }

    /**
     * @param int $length
     * @return Constraint
     */
    public function blob(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('blob');
        }

        return $this->datatype = new Constraint("blob($length)");
    }

    /**
     * @param string[] $enums
     * @return Constraint
     */
    public function enum(array $enums): Constraint
    {
        $enums = array_map(fn ($item) => "'{$item}'", $enums);
        $enum  = implode(', ', $enums);

        return $this->datatype = new Constraint("ENUM ({$enum})");
    }

    /**
     * @param string $column
     * @return void
     */
    public function after(string $column): void
    {
        $this->datatype = "AFTER {$column}";
    }

    /**
     * @return void
     */
    public function first(): void
    {
        $this->datatype = 'FIRST';
    }
}
