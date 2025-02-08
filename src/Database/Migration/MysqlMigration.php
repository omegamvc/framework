<?php

/**
 * Part of Omega - Database Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Database\Migration;

use Omega\Database\Adapter\DatabaseAdapterInterface;
use Omega\Database\Exception\MigrationException;
use Omega\Database\Migration\Field\AbstractField;
use Omega\Database\Migration\Field\BoolField;
use Omega\Database\Migration\Field\DateTimeField;
use Omega\Database\Migration\Field\FloatField;
use Omega\Database\Migration\Field\IdField;
use Omega\Database\Migration\Field\IntField;
use Omega\Database\Migration\Field\StringField;
use Omega\Database\Migration\Field\TextField;

use function array_filter;
use function array_map;
use function join;

/**
 * Mysql migration class.
 *
 * The `MysqlMigration` class handles SQLite database migrations, creating
 * or altering tables.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Migration
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class MysqlMigration extends AbstractMigration
{
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
        parent::__construct($connection, $table, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $fields = array_map(fn($field) => $this->stringForField($field), $this->fields);

        $primary    = array_filter($this->fields, fn($field) => $field instanceof IdField);
        $primaryKey = isset($primary[0]) ? "PRIMARY KEY (`{$primary[0]->name}`)" : '';

        $query = '';

        /**       if ( $this->type === 'create' ) {.
                    $fields = join( PHP_EOL, array_map( fn( $field ) => "{$field},", $fields ) );

                    $query .= "
                        CREATE TABLE `{$this->table}` (
                            {$fields}
                            {$primaryKey}
                        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
                    ";
                }

                if ( $this->type === 'alter' ) {
                    $fields = is_array($fields) ? $fields : [$fields];

                $fields = join(PHP_EOL, array_map(fn($field) => "{$field};", $fields));
                $drops  = join(PHP_EOL, array_map(fn($drop) => "DROP COLUMN `{$drop}`;", $this->drops));

                    $query .= "
                        ALTER TABLE `{$this->table}`
                        {$fields}
                        {$drops}
                    ";
                }*/
        if ($this->type === 'create') {
            $fields = join(PHP_EOL, array_map(fn($field) => "{$field},", $fields));
            $query  = "
                CREATE TABLE `{$this->table}` (
                    {$fields}
                    {$primaryKey}
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
            ";
        }

        if ($this->type === 'alter') {
            $fields = join(PHP_EOL, array_map(fn($field) => "{$field};", $fields));
            $drops  = join(PHP_EOL, array_map(fn($drop) => "DROP COLUMN `{$drop}`;", $this->drops));
            $query  = "
                ALTER TABLE `{$this->table}`
                {$fields}
                {$drops}
            ";
        }

        $statement = $this->connection->pdo()->prepare($query);
        $statement->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        if ($this->type === 'create') {
            $query = "DROP TABLE IF EXISTS `{$this->table}`";
        }

        if ($this->type === 'alter') {
            $drops = join(PHP_EOL, array_map(fn($drop) => "DROP COLUMN `{$drop}`;", $this->drops));
            $query = "
                ALTER TABLE `{$this->table}`
                {$drops}
            ";
        }

        $statement = $this->connection->pdo()->prepare($query);
        $statement->execute();
    }

    /**
     * {@inheritdoc}
     * @throws MigrationException if field is not exists.
     */
    public function stringForField(AbstractField $field): string
    {
        $prefix = '';

        if ($this->type === 'alter') {
            $prefix = 'ADD';
        }

        if ($field->alter) {
            $prefix = 'MODIFY';
        }

        if ($field instanceof BoolField) {
            $template = "{$prefix} `{$field->name}` tinyint(4)";

            if ($field->nullable) {
                $template .= ' DEFAULT NULL';
            }

            if ($field->default !== null) {
                $default = (int)$field->default;
                $template .= " DEFAULT {$default}";
            }

            return $template;
        }

        if ($field instanceof DateTimeField) {
            $template = "{$prefix} `{$field->name}` datetime";

            if ($field->nullable) {
                $template .= ' DEFAULT NULL';
            }

            if ($field->default === 'CURRENT_TIMESTAMP') {
                $template .= ' DEFAULT CURRENT_TIMESTAMP';
            } elseif ($field->default !== null) {
                $template .= " DEFAULT '{$field->default}'";
            }

            return $template;
        }

        if ($field instanceof FloatField) {
            $template = "{$prefix} `{$field->name}` float";

            if ($field->nullable) {
                $template .= ' DEFAULT NULL';
            }

            if ($field->default !== null) {
                $template .= " DEFAULT '{$field->default}'";
            }

            return $template;
        }

        if ($field instanceof IdField) {
            return "{$prefix} `{$field->name}` int(11) unsigned NOT NULL AUTO_INCREMENT";
        }

        if ($field instanceof IntField) {
            $template = "{$prefix} `{$field->name}` int(11)";

            if ($field->nullable) {
                $template .= ' DEFAULT NULL';
            }

            if ($field->default !== null) {
                $template .= " DEFAULT '{$field->default}'";
            }

            return $template;
        }

        if ($field instanceof StringField) {
            $template = "{$prefix} `{$field->name}` varchar(255)";

            if ($field->nullable) {
                $template .= ' DEFAULT NULL';
            }

            if ($field->default !== null) {
                $template .= " DEFAULT '{$field->default}'";
            }

            return $template;
        }

        if ($field instanceof TextField) {
            return "{$prefix} `{$field->name}` text";
        }

        throw new MigrationException(
            "Unrecognised field type for {$field->name}"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function dropColumn(string $name): static
    {
        $this->drops[] = $name;

        return $this;
    }
}
