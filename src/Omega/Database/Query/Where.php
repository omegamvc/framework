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

namespace Omega\Database\Query;

use Omega\Database\Query\Traits\ConditionTrait;
use Omega\Database\Query\Traits\SubQueryTrait;

/**
 * WHERE clause builder.
 *
 * This class allows you to construct SQL WHERE conditions using filters, raw conditions,
 * strict/loose mode logic, and support for subqueries. It stores the conditions and bindings
 * to be later injected into a full query.
 *
 * @category   Omega
 * @package    Database
 * @subpackage Query
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class Where
{
    use ConditionTrait;
    use SubQueryTrait;

    /**
     * Table name used for condition generation.
     *
     * @var string
     */
    private string $table;

    /**
     * Optional subquery for alias or nested filtering.
     *
     * @var InnerQuery|null
     */
    private ?InnerQuery $subQuery = null;

    /**
     * Bindings for the prepared statement.
     *
     * @var Bind[]
     */
    private array $binds = [];

    /**
     * Raw WHERE conditions.
     *
     * @var string[]
     */
    private array $where = [];

    /**
     * Key-value filters used to generate SQL WHERE logic.
     *
     * @var array<string, string>
     */
    private array $filters = [];

    /**
     * Whether to apply strict matching (AND) or loose matching (OR).
     *
     * @var bool
     */
    private bool $strictMode = true;

    /**
     * Constructor.
     *
     * @param string $tableName Name of the target table
     */
    public function __construct(string $tableName)
    {
        $this->table = $tableName;
    }

    /**
     * Get all internal condition values in a single array.
     *
     * Includes:
     * - binds (Bind[])
     * - where (string[])
     * - filters (array<string, string>)
     * - isStrict (bool)
     *
     * @return array<string, Bind[]|string[]|array<string, string>|bool>
     */
    public function get(): array
    {
        return [
            'binds'     => $this->binds,
            'where'     => $this->where,
            'filters'   => $this->filters,
            'isStrict'  => $this->strictMode,
        ];
    }

    /**
     * Flush the current condition state.
     *
     * Clears all binds, where conditions, filters, and resets strict mode.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->binds      = [];
        $this->where      = [];
        $this->filters    = [];
        $this->strictMode = true;
    }

    /**
     * Check whether the current where object is empty.
     *
     * @return bool True if no filters, where conditions, or binds are set
     */
    public function isEmpty(): bool
    {
        return [] === $this->binds && [] === $this->where && [] === $this->filters && true === $this->strictMode;
    }
}
