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
 * Field interface.
 *
 * The `FieldInterface` defines the contract for database migration field classes,
 * providing methods to determine if the field is nullable or alterable.
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
interface FieldInterface
{
    /**
     * Determine if the field is nullable.
     *
     * @return $this
     */
    public function nullable(): static;

    /**
     * Determine if the field is alterable.
     *
     * @return $this
     */
    public function alter(): static;
}
