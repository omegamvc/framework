<?php

/**
 * Part of vOmega - Database Package.
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
 * Boolean field class.
 *
 * The `BoolField` represents a boolean field for database migrations.
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
     * @param bool $value Holds the default value for the boolean field.
     * @return $this Returns the current instance for method chaining.
     */
    public function default(bool $value): static
    {
        $this->default = $value;

        return $this;
    }
}
