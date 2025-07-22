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

namespace Omega\Database\Schema\Table\Attributes;

/**
 * Class DataType
 *
 * Represents the definition of a database column including its name and data type.
 * This class provides a fluent interface for setting the data type of the column,
 * returning a `Constraint` object for further customization such as default values,
 * nullability, auto-increment, etc.
 *
 * It is typically used when programmatically defining table schemas (e.g. during migrations).
 *
 * @category   Omega
 * @package    Database
 * @subpackage Schema\Table\Attributes
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
     * The data type assigned to the column, with constraints.
     *
     * @var string|Constraint
     */
    private string|Constraint $datatype;

    /**
     * Create a new DataType instance for the specified column.
     *
     * @param string $columnName The name of the column.
     */
    public function __construct(string $columnName)
    {
        $this->name     = $columnName;
        $this->datatype = '';
    }

    /**
     * Convert the DataType instance to an SQL string.
     *
     * @return string SQL fragment representing the column and its type.
     */
    public function __toString(): string
    {
        return $this->query();
    }

    /**
     * Build the SQL column definition string.
     *
     * @return string
     */
    private function query(): string
    {
        return $this->name . ' ' . $this->datatype;
    }

    // ───── Numeric Types ─────

    /**
     * Define an INT column.
     *
     * @param int $length Optional length for the type (e.g. int(11)).
     * @return Constraint
     */
    public function int(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('int') : new Constraint("int($length)");
    }

    /**
     * Define a TINYINT column.
     */
    public function tinyint(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('tinyint') : new Constraint("tinyint($length)");
    }

    /**
     * Define a SMALLINT column.
     */
    public function smallint(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('smallint') : new Constraint("smallint($length)");
    }

    /**
     * Define a BIGINT column.
     */
    public function bigint(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('bigint') : new Constraint("bigint($length)");
    }

    /**
     * Define a FLOAT column.
     */
    public function float(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('float') : new Constraint("float($length)");
    }

    // ───── Date and Time Types ─────

    /**
     * Define a TIME column.
     */
    public function time(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('time') : new Constraint("time($length)");
    }

    /**
     * Define a TIMESTAMP column.
     */
    public function timestamp(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('timestamp') : new Constraint("timestamp($length)");
    }

    /**
     * Define a DATE column.
     */
    public function date(): Constraint
    {
        return $this->datatype = new Constraint('date');
    }

    // ───── Text and Binary Types ─────

    /**
     * Define a VARCHAR column.
     */
    public function varchar(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('varchar') : new Constraint("varchar($length)");
    }

    /**
     * Define a TEXT column.
     */
    public function text(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('text') : new Constraint("text($length)");
    }

    /**
     * Define a BLOB column.
     */
    public function blob(int $length = 0): Constraint
    {
        return $this->datatype = $length === 0 ? new Constraint('blob') : new Constraint("blob($length)");
    }

    /**
     * Define an ENUM column.
     *
     * @param string[] $enums Array of allowed string values.
     * @return Constraint
     */
    public function enum(array $enums): Constraint
    {
        $enums = array_map(fn ($item) => "'{$item}'", $enums);
        $enum  = implode(', ', $enums);

        return $this->datatype = new Constraint("ENUM ({$enum})");
    }
}
