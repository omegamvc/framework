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
use Omega\Database\Exception\MigrationException;
use Omega\Database\Migration\Field\AbstractField;
use Omega\Database\Migration\Field\BoolField;
use Omega\Database\Migration\Field\DateTimeField;
use Omega\Database\Migration\Field\FloatField;
use Omega\Database\Migration\Field\IdField;
use Omega\Database\Migration\Field\IntField;
use Omega\Database\Migration\Field\StringField;
use Omega\Database\Migration\Field\TextField;

use function array_map;
use function join;

/**
 * Sqlite migration class.
 *
 * The `SqliteMigration` class handles SQLite database migrations, creating
 * or altering tables.
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
class SqliteMigration extends AbstractMigration
{
    /**
     * MysqlMigration class constructor.
     *
     * @param DatabaseAdapterInterface $connection Holds an instance of Mysql.
     * @param string                   $table      Holds the table name.
     * @param string                   $type       Holds the query type.
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
        $command = $this->type === 'create' ? '' : 'ALTER TABLE';

        $fields = array_map(fn($field) => $this->stringForField($field), $this->fields);

        $query = '';

        if ($this->type === 'create') {
            $fields = join(',' . PHP_EOL, $fields);

            $query .= "
                CREATE TABLE \"{$this->table}\" (
                    {$fields}
                );
            ";
        } elseif ($this->type === 'alter') {
            $fields = join(';' . PHP_EOL, $fields);

            $query .= "
                ALTER TABLE \"{$this->table}\"
                {$fields};
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
            $query = "DROP TABLE IF EXISTS \"{$this->table}\"";
        }

        if ($this->type === 'alter') {
            $drops = join(PHP_EOL, array_map(fn($drop) => "DROP COLUMN \"{$drop}\";", $this->drops));
            $query = "
                ALTER TABLE \"{$this->table}\"
                {$drops}
            ";
        }

        $statement = $this->connection->pdo()->prepare($query);
        $statement->execute();
    }

    /**
     * {@inheritdoc}
     * @throws MigrationException if field not exists.
     */
    public function stringForField(AbstractField $field): string
    {
        $prefix = '';

        if ($this->type === 'alter') {
            $prefix = 'ADD COLUMN';
        }

        if ($field->alter) {
            throw new MigrationException('SQLite does\'t support altering columns');
        }

        if ($field instanceof BoolField) {
            $template = "{$prefix} \"{$field->name}\" INTEGER";

            if (! $field->nullable) {
                $template .= ' NOT NULL';
            }

            if ($field->default !== null) {
                $default = (int)$field->default;
                $template .= " DEFAULT {$default}";
            }

            return $template;
        }

        if ($field instanceof DateTimeField) {
            $template = "{$prefix} \"{$field->name}\" TEXT";

            if (! $field->nullable) {
                $template .= ' NOT NULL';
            }

            if ($field->default === 'CURRENT_TIMESTAMP') {
                $template .= ' DEFAULT CURRENT_TIMESTAMP';
            } elseif ($field->default !== null) {
                $template .= " DEFAULT '{$field->default}'";
            }

            return $template;
        }

        if ($field instanceof FloatField) {
            $template = "{$prefix} \"{$field->name}\" REAL";

            if (! $field->nullable) {
                $template .= ' NOT NULL';
            }

            if ($field->default !== null) {
                $template .= " DEFAULT {$field->default}";
            }

            return $template;
        }

        if ($field instanceof IdField) {
            return "{$prefix} \"{$field->name}\" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE";
        }

        if ($field instanceof IntField) {
            $template = "{$prefix} \"{$field->name}\" INTEGER";

            if (! $field->nullable) {
                $template .= ' NOT NULL';
            }

            if ($field->default !== null) {
                $template .= " DEFAULT {$field->default}";
            }

            return $template;
        }

        if ($field instanceof StringField || $field instanceof TextField) {
            $template = "{$prefix} \"{$field->name}\" TEXT";

            if (! $field->nullable) {
                $template .= ' NOT NULL';
            }

            if ($field->default !== null) {
                $template .= " DEFAULT '{$field->default}'";
            }

            return $template;
        }

        throw new MigrationException("Unrecognised field type for {$field->name}");
    }

    /**
     * {@inheritdoc}
     * @throws MigrationException id attempt to drop columns in Sqlite.
     */
    public function dropColumn(string $name): static
    {
        throw new MigrationException(
            'SQLite does\'t support dropping columns'
        );
    }
}
