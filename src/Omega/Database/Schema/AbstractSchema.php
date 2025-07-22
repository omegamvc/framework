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

namespace Omega\Database\Schema;

use function array_filter;
use function implode;

/**
 * Abstract base class for building SQL schema operations.
 *
 * This class provides the foundation for concrete schema builders,
 * such as table creation or alteration. It includes basic utilities
 * for building and executing SQL statements.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Schema
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
abstract class AbstractSchema
{
    /** @var SchemaConnection PDO connection for executing schema queries. */
    protected SchemaConnection $pdo;

    /**
     * Convert the schema object to a string representation.
     *
     * This is typically the generated SQL query.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->builder();
    }

    /**
     * Build and return the SQL schema string.
     *
     * This method should be implemented by concrete subclasses
     * to generate the appropriate SQL statement.
     *
     * @return string
     */
    protected function builder(): string
    {
        return '';
    }

    /**
     * Execute the generated SQL schema query.
     *
     * @return bool True on success, false on failure.
     */
    public function execute(): bool
    {
        return $this->pdo->query($this->builder())->execute();
    }

    /**
     * Helper method: joins array elements into a string with a separator,
     * skipping empty strings.
     *
     * @param string[] $array     Array of string elements to join.
     * @param string   $separator Separator to use between elements.
     *
     * @return string The joined string.
     */
    protected function join(array $array, string $separator = ' '): string
    {
        return implode(
            $separator,
            array_filter($array, fn ($item) => $item !== '')
        );
    }
}
