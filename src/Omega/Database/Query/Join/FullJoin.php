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
 * Represents a SQL FULL OUTER JOIN clause.
 * Returns all records when there is a match in either table.
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
class FullJoin extends AbstractJoin
{
    /**
     * Build the FULL OUTER JOIN clause.
     *
     * @return string The raw SQL FULL OUTER JOIN clause with ON condition.
     */
    protected function joinBuilder(): string
    {
        return "FULL OUTER JOIN {$this->getAlias()} ON {$this->splitJoin()}";
    }
}
