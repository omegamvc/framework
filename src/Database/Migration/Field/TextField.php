<?php

/**
 * Part of Omega CMS - Database Package.
 *
 * @see       https://omegacms.github.io
 *
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Database\Migration\Field;

/*
 * @use
 */
use Omega\Database\Exception\MigrationException;

/**
 * Text field class.
 *
 * The `TextField` represents a text field for database migrations.
 *
 * @category    Omega
 * @package     Database
 * @subpackage  Migration\Field
 *
 * @see        https://omegacms.github.io
 *
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class TextField extends AbstractField
{
    /**
     * Default value.
     *
     * @var string|null Holds the default value or null.
     */
    public ?string $default = null;

    /**
     * Determine if the field is nullable.
     *
     * @return $this
     *
     * @throws MigrationException if attempt to set text field nullable.
     */
    public function nullable(): static
    {
        throw new MigrationException('Text fields cannot be nullable');
    }

    /**
     * Set the default value for string field.
     *
     * @param string $value Holds the default value for the string field.
     *
     * @return $this Returns the current instance for method chaining.
     */
    public function default(string $value): static
    {
        $this->default = $value;

        return $this;
    }
}
