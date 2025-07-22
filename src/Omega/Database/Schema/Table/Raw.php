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

use Omega\Database\Schema\SchemaConnection;
use Omega\Database\Schema\AbstractSchema;

/**
 * Class Raw
 *
 * Represents a raw SQL statement within the schema builder system.
 * This class allows execution of any custom or unsupported SQL command
 * directly, bypassing the fluent schema builder abstraction.
 *
 * Useful for advanced or vendor-specific operations that are not covered
 * by the provided schema classes.
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
class Raw extends AbstractSchema
{
    /**
     * The raw SQL query string to be executed.
     *
     * @var string
     */
    private string $raw;

    /**
     * Raw constructor.
     *
     * @param string            $raw The raw SQL statement.
     * @param SchemaConnection  $pdo The schema-level PDO connection.
     */
    public function __construct(string $raw, SchemaConnection $pdo)
    {
        $this->raw = $raw;
        $this->pdo = $pdo;
    }

    /**
     * Returns the raw SQL string as-is.
     *
     * @return string The raw SQL query.
     */
    protected function builder(): string
    {
        return $this->raw;
    }
}
