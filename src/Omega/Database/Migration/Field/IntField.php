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
 * Integer field class.
 *
 * The `IntField` represents a string field for database migrations.
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
class IntField extends AbstractField
{
    /**
     * Default value for the int field.
     *
     * @var int|null Holds the default value or null.
     */
    public ?int $default = null;

    /**
     * Set the default value for int field.
     *
     * @param int $value Holds the default value for the int field.
     * @return $this Returns the current instance for method chaining.
     */
    public function default(int $value): static
    {
        $this->default = $value;

        return $this;
    }
}
