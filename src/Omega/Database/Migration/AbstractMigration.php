<?php

/**
 * Part of Omega - Database Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Migration;

use Omega\Database\Adapter\DatabaseAdapterInterface;
use Omega\Database\Migration\Field\AbstractField;
use Omega\Database\Migration\Field\BoolField;
use Omega\Database\Migration\Field\DateTimeField;
use Omega\Database\Migration\Field\FloatField;
use Omega\Database\Migration\Field\IdField;
use Omega\Database\Migration\Field\IntField;
use Omega\Database\Migration\Field\StringField;
use Omega\Database\Migration\Field\TextField;

/**
 * Abstract migration class.
 *
 * The `AbstractMigration` class provides a foundation for creating database
 * migrations with various field types.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Migration
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
abstract class AbstractMigration implements MigrationInterface
{
    /**
     * Fields array.
     *
     * @var array<AbstractField> Holds an array of fields.
     */
    public array $fields = [];

    /**
     * Table name.
     *
     * @var string Holds the table name.
     */
    public string $table;

    /**
     * Query type.
     *
     * @var string Holds the query type-
     */
    public string $type;

    /**
     * Drops columns.
     *
     * @var array<string> Holds an array of drops columns.
     */
    public array $drops = [];

    /**
     * MysqlMigration class constructor.
     *
     * @param DatabaseAdapterInterface $connection Holds an instance of Mysql.
     * @param string                   $table      Holds the table name.
     * @param string                   $type       Holds the query type.
     *
     * @return void
     */
    public function __construct(protected DatabaseAdapterInterface $connection, string $table, string $type)
    {
        $this->table      = $table;
        $this->type       = $type;
    }

    /**
     * Set boolean field.
     *
     * @param string $name Holds the field name.
     * @return BoolField Return an instance of BoolField.
     */
    public function bool(string $name): BoolField
    {
        return $this->fields[] = new BoolField($name);
    }

    /**
     * Set date time field.
     *
     * @param string $name Holds the field name.
     * @return DateTimeField Return an instance of DateTimeField.
     */
    public function dateTime(string $name): DateTimeField
    {
        return $this->fields[] = new DateTimeField($name);
    }

    /**
     * Set float field.
     *
     * @param string $name Holds the field name.
     * @return FloatField Return an instance of FloatField.
     */
    public function float(string $name): FloatField
    {
        return $this->fields[] = new FloatField($name);
    }

    /**
     * Set id field.
     *
     * @param string $name Holds the field name.
     * @return IdField Return an instance of IdField.
     */
    public function id(string $name): IdField
    {
        return $this->fields[] = new IdField($name);
    }

    /**
     * Set int field.
     *
     * @param string $name Holds the field name.
     * @return IntField Return an instance of IntField.
     */
    public function int(string $name): IntField
    {
        return $this->fields[] = new IntField($name);
    }

    /**
     * Set string field.
     *
     * @param string $name Holds the field name.
     * @return StringField Return an instance of StringField.
     */
    public function string(string $name): StringField
    {
        return $this->fields[] = new StringField($name);
    }

    /**
     * Set text field.
     *
     * @param string $name Holds the field name.
     * @return TextField Return an instance of TextField.
     */
    public function text(string $name): TextField
    {
        return $this->fields[] = new TextField($name);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function execute(): void;

    /**
     * {@inheritdoc}
     */
    abstract public function down(): void;

    /**
     * {@inheritdoc}
     */
    abstract public function stringForField(AbstractField $field): string;

    /**
     * {@inheritdoc}
     */
    abstract public function dropColumn(string $name): static;
}
