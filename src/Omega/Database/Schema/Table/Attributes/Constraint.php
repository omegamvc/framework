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

use Exception;

/**
 * Class Constraint
 *
 * Represents SQL column constraints and attributes for table schema definitions.
 * Allows building constraint clauses such as nullable, default value, auto-increment,
 * unsigned, ordering, and raw SQL fragments for a column datatype.
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
class Constraint
{
    /** @var string SQL data type of the column (e.g., int, varchar). */
    protected string $dataType;

    /** @var string Nullable constraint string ("NOT NULL" or "NULL"). */
    protected string $nullable;

    /** @var string Default value constraint string (e.g., "DEFAULT 'value'"). */
    protected string $default;

    /** @var string Auto-increment constraint string ("AUTO_INCREMENT" or empty). */
    protected string $autoIncrement;

    /** @var string Order clause string for the column. */
    protected string $order;

    /** @var string Unsigned attribute for integer types. */
    protected string $unsigned;

    /** @var string Raw SQL snippet appended to the constraint. */
    protected string $raw;

    /**
     * Constructor.
     *
     * @param string $dataType The SQL data type for the column.
     */
    public function __construct(string $dataType)
    {
        $this->dataType     = $dataType;
        $this->nullable      = '';
        $this->default       = '';
        $this->autoIncrement = '';
        $this->raw           = '';
        $this->order         = '';
        $this->unsigned      = '';
    }

    /**
     * Return the assembled SQL constraint string.
     *
     * @return string The full SQL constraint definition for the column.
     */
    public function __toString(): string
    {
        return $this->query();
    }

    /**
     * Build the SQL constraint string from all attributes.
     *
     * @return string
     */
    private function query(): string
    {
        $column = [
            $this->dataType,
            $this->unsigned,
            $this->nullable,
            $this->default,
            $this->autoIncrement,
            $this->raw,
            $this->order,
        ];

        return implode(' ', array_filter($column, fn ($item) => $item !== ''));
    }

    /**
     * Set NOT NULL or NULL constraint.
     *
     * @param bool $notNull True for NOT NULL, false for NULL.
     * @return self
     */
    public function notNull(bool $notNull = true): self
    {
        $this->nullable = $notNull ? 'NOT NULL' : 'NULL';

        return $this;
    }

    /**
     * Set nullable constraint (alias of notNull).
     *
     * @param bool $null True for NULL, false for NOT NULL.
     * @return self
     */
    public function null(bool $null = true): self
    {
        return $this->notNull(!$null);
    }

    /**
     * Set default value constraint.
     *
     * @param int|string $default Default value to set.
     * @param bool       $wrap    Whether to wrap string default value in quotes (ignored for integers).
     * @return self
     */
    public function default(int|string $default, bool $wrap = true): self
    {
        $wrap          = is_int($default) ? false : $wrap;
        $this->default = $wrap ? "DEFAULT '{$default}'" : "DEFAULT {$default}";

        return $this;
    }

    /**
     * Set default value to NULL.
     *
     * @return self
     */
    public function defaultNull(): self
    {
        return $this->default('NULL', false);
    }

    /**
     * Enable or disable AUTO_INCREMENT.
     *
     * @param bool $increment True to enable auto-increment.
     * @return self
     */
    public function autoIncrement(bool $increment = true): self
    {
        $this->autoIncrement = $increment ? 'AUTO_INCREMENT' : '';

        return $this;
    }

    /**
     * Alias for autoIncrement method.
     *
     * @param bool $increment
     * @return self
     */
    public function increment(bool $increment): self
    {
        return $this->autoIncrement($increment);
    }

    /**
     * Mark data type as UNSIGNED (only valid for integer types).
     *
     * @throws Exception If called on non-integer datatype.
     * @return self
     */
    public function unsigned(): self
    {
        if (false === preg_match('/^(int|tinyint|bigint|smallint)(\d+)?$/', $this->dataType)) {
            throw new Exception('Cannot use UNSIGNED on non-integer datatype.');
        }
        $this->unsigned = 'UNSIGNED';

        return $this;
    }

    /**
     * Set raw SQL string appended to the constraint.
     *
     * @param string $raw Raw SQL fragment.
     * @return self
     */
    public function raw(string $raw): self
    {
        $this->raw = $raw;

        return $this;
    }
}
