<?php

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

namespace Omega\Database\Schema\Table;

use Omega\Database\Schema\Table\Attributes\Alter\DataType as AlterDataType;
use Omega\Database\Schema\Table\Attributes\DataType;

/**
 * Class Column
 *
 * Represents a column definition used in table schema creation or alteration.
 * This class serves as a wrapper for different types of column definitions, such as
 * creating new columns, altering existing ones, or injecting raw SQL.
 *
 * It allows flexible composition of column expressions to be included in SQL statements.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Schema\Table
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Column
{
    /**
     * The column representation, which can be a string (raw SQL),
     * a DataType instance for new columns, or an AlterDataType for existing columns.
     *
     * @var string|DataType|AlterDataType
     */
    protected string|AlterDataType|DataType $query;

    /**
     * Convert the column definition to string for SQL usage.
     *
     * @return string SQL string representation of the column.
     */
    public function __toString(): string
    {
        return (string) $this->query;
    }

    /**
     * Define a new column using the given column name.
     *
     * @param string $columnName The name of the new column.
     * @return DataType An object to configure the column data type and constraints.
     */
    public function column(string $columnName): DataType
    {
        return $this->query = new DataType($columnName);
    }

    /**
     * Define an existing column to be altered using the given column name.
     *
     * @param string $columnName The name of the column to alter.
     * @return AlterDataType An object to configure the column changes.
     */
    public function alterColumn(string $columnName): AlterDataType
    {
        return $this->query = new AlterDataType($columnName);
    }

    /**
     * Set a raw SQL column definition.
     *
     * @param string $query The raw SQL query string.
     * @return $this
     */
    public function raw(string $query): self
    {
        $this->query = $query;

        return $this;
    }
}
