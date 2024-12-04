<?php

/**
 * Part of Omega - Database Package.
 *
 * @see       https://omegamvc.github.io
 *
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
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
 * Field interface.
 *
 * Thie `FieldInterface` defines the contract for database migration field classes,
 * providing methods to determine if the field is nullable or alterable.
 *
 * @category    Omega
 * @package     Database
 * @subpackage  Migration\Field
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
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
