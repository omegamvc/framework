<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

/**
 * Part of Omega - Database Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Schema\Table\Attributes\Alter;

/**
 * Class DataType
 *
 * Represents a database column definition with its associated data type
 * and optional constraints. This class is responsible for generating
 * the SQL syntax needed to define a column in a CREATE or ALTER statement.
 *
 * The methods dynamically create an instance of `Constraint` for each supported
 * data type and allow further customization of the column's behavior, such as nullability,
 * default value, auto-increment, position, and more.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Schema\Table\Attributes\Alter
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class DataType
{
    /**
     * The name of the column.
     *
     * @var string
     */
    private string $name;

    /**
     * The data type definition or column constraint.
     *
     * @var string|Constraint
     */
    private string|Constraint $datatype;

    /**
     * Initialize a new column data type with its name.
     *
     * @param string $columnName The name of the column.
     */
    public function __construct(string $columnName)
    {
        $this->name     = $columnName;
        $this->datatype = '';
    }

    /**
     * Casts the object to a string by building the SQL column definition.
     *
     * @return string SQL representation of the column.
     */
    public function __toString(): string
    {
        return $this->query();
    }

    /**
     * Build and return the full SQL statement for the column definition.
     *
     * @return string
     */
    private function query(): string
    {
        return $this->name . ' ' . $this->datatype;
    }

    // ───── Numeric Types ─────

    /**
     * Define an `INT` column with optional length.
     */
    public function int(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('int') : new Constraint("int($length)");
    }

    /**
     * Define a `TINYINT` column with optional length.
     */
    public function tinyint(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('tinyint') : new Constraint("tinyint($length)");
    }

    /**
     * Define a `SMALLINT` column with optional length.
     */
    public function smallint(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('smallint') : new Constraint("smallint($length)");
    }

    /**
     * Define a `BIGINT` column with optional length.
     */
    public function bigint(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('bigint') : new Constraint("bigint($length)");
    }

    /**
     * Define a `FLOAT` column with optional length.
     */
    public function float(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('float') : new Constraint("float($length)");
    }

    // ───── Date & Time Types ─────

    /**
     * Define a `TIME` column with optional fractional seconds precision.
     */
    public function time(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('time') : new Constraint("time($length)");
    }

    /**
     * Define a `TIMESTAMP` column with optional fractional seconds precision.
     */
    public function timestamp(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('timestamp') : new Constraint("timestamp($length)");
    }

    /**
     * Define a `DATE` column.
     */
    public function date(): Constraint
    {
        return $this->datatype = new Constraint('date');
    }

    // ───── String/Text Types ─────

    /**
     * Define a `VARCHAR` column with optional length.
     */
    public function varchar(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('varchar') : new Constraint("varchar($length)");
    }

    /**
     * Define a `TEXT` column with optional length.
     */
    public function text(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('text') : new Constraint("text($length)");
    }

    /**
     * Define a `BLOB` column with optional length.
     */
    public function blob(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('blob') : new Constraint("blob($length)");
    }

    /**
     * Define an `ENUM` column with a set of allowed string values.
     *
     * @param string[] $enums The list of possible values.
     * @return Constraint
     */
    public function enum(array $enums): Constraint
    {
        $enums = array_map(fn ($item) => "'{$item}'", $enums);
        $enum  = implode(', ', $enums);

        return $this->datatype = new Constraint("ENUM ({$enum})");
    }

    // ───── Column Positioning (DDL only) ─────

    /**
     * Set the column to appear after another column in the table.
     *
     * @param string $column The name of the reference column.
     */
    public function after(string $column): void
    {
        $this->datatype = "AFTER {$column}";
    }

    /**
     * Set the column to appear as the first column in the table.
     */
    public function first(): void
    {
        $this->datatype = 'FIRST';
    }
}
