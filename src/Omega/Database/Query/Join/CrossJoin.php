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
 * Represents a SQL CROSS JOIN clause.
 * Produces the Cartesian product of the joined tables without any condition.
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
class CrossJoin extends AbstractJoin
{
    /**
     * Build the CROSS JOIN clause.
     *
     * @return string The raw SQL CROSS JOIN clause.
     */
    protected function joinBuilder(): string
    {
        return "CROSS JOIN {$this->getAlias()}";
    }
}
