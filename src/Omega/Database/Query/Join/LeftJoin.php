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

namespace Omega\Database\Query\Join;

/**
 * Represents a SQL LEFT JOIN clause.
 * Returns all records from the left table, and matched records from the right table.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Query\Join
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class LeftJoin extends AbstractJoin
{
    /**
     * Build the LEFT JOIN clause.
     *
     * @return string The raw SQL LEFT JOIN clause with ON condition.
     */
    protected function joinBuilder(): string
    {
        return "LEFT JOIN {$this->getAlias()} ON {$this->splitJoin()}";
    }
}
