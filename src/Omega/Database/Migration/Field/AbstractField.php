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

namespace Omega\Database\Migration\Field;

/**
 * Abstract field class.
 *
 * The `AbstractField` class serves as the base for all database migration
 * fields, providing common functionality such as nullable and alterable
 * properties.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Migration\Field
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
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
     */
    public function nullable(): static
    {
        $this->nullable = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function alter(): static
    {
        $this->alter = true;

        return $this;
    }
}
