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

/**
 * Abstract field class.
 *
 * The `AbstractField` class serves as the base for all database migration
 * fields, providing common functionality such as nullable and alterable
 * properties.
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
abstract class AbstractField implements FieldInterface
{
    /**
     * Field name.
     *
     * @var string Holds the field name.
     */
    public string $name;

    /**
     * Nullable field.
     *
     * @var bool Determine if field is nullable.
     */
    public bool $nullable = false;

    /**
     * Alterable field.
     *
     * @var bool Determine if field is alterable.
     */
    public bool $alter = false;

    /**
     * AbstractField class constructor.
     *
     * @param string $name Holds the field name.
     *
     * @return void
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function nullable(): static
    {
        $this->nullable = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function alter(): static
    {
        $this->alter = true;

        return $this;
    }
}
