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

namespace Omega\Database\Schema\DB;

use Omega\Database\Schema\SchemaConnection;
use Omega\Database\Schema\AbstractSchema;
use Omega\Database\Schema\Traits\ConditionTrait;

/**
 * Class Drop
 *
 * Builds and executes a SQL statement to drop a database schema.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Schema\DB
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Drop extends AbstractSchema
{
    use ConditionTrait;

    /**
     * Name of the database to drop.
     *
     * @var string
     */
    private string $databaseName;

    /**
     * Drop constructor.
     *
     * @param string           $databaseName Name of the database to drop.
     * @param SchemaConnection $pdo          Schema connection instance for executing the query.
     */
    public function __construct(string $databaseName, SchemaConnection $pdo)
    {
        $this->databaseName = $databaseName;
        $this->pdo          = $pdo;
    }

    /**
     * Builds the SQL DROP DATABASE query string.
     *
     * @return string The complete DROP DATABASE SQL statement.
     */
    protected function builder(): string
    {
        $condition = $this->join([$this->ifExists, $this->databaseName]);

        return 'DROP DATABASE ' . $condition . ';';
    }
}
