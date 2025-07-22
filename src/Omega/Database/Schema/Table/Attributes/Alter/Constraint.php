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

namespace Omega\Database\Schema\Table\Attributes\Alter;

use Omega\Database\Schema\Table\Attributes\Constraint as AttributesConstraint;

/**
 * Class Constraint
 *
 * Extends the base column constraint functionality by adding
 * column positioning clauses for SQL table definitions.
 * Allows defining the column order using `FIRST` or `AFTER column_name`
 * and supports raw SQL fragments for additional customization.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Schema\Table\Attributes\Alter
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Constraint extends AttributesConstraint
{
    /**
     * Set the position of the column to appear after another column.
     *
     * @param string $column The name of the reference column.
     * @return self
     */
    public function after(string $column): self
    {
        $this->order = "AFTER {$column}";

        return $this;
    }

    /**
     * Set the position of the column as the first column in the table.
     *
     * @return self
     */
    public function first(): self
    {
        $this->order = 'FIRST';

        return $this;
    }

    /**
     * Set raw SQL fragment appended to the constraint definition.
     * Overrides any previously defined raw fragment.
     *
     * @param string $raw The raw SQL string to append.
     * @return self
     */
    public function raw(string $raw): self
    {
        $this->raw = $raw;

        return $this;
    }
}
