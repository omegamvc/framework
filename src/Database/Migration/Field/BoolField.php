<?php

/**
 * Part of vOmega CMS - Database Package.
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

/**
 * Boolean field class.
 *
 * The `BoolField` represents a boolean field for database migrations.
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
class BoolField extends AbstractField
{
    /**
     * Default value for the boolean field.
     *
     * @var bool|null Holds the default value or null.
     */
    public ?bool $default = null;

    /**
     * Set the default value for boolean field.
     *
     * @param bool $value Holds the default value for the booelean field.
     *
     * @return $this Returns the current instance for method chaining.
     */
    public function default(bool $value): static
    {
        $this->default = $value;

        return $this;
    }
}
