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

/**
 * Abstract base class for query builders that execute SQL statements.
 *
 * Provides a unified implementation of the `execute()` method for
 * queries that perform write operations (INSERT, UPDATE, DELETE).
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
abstract class AbstractExecute extends AbstractQuery
{
    /**
     * Execute the prepared SQL statement.
     *
     * Builds the query string, binds the parameters, and runs the query using PDO.
     *
     * @return bool True if rows were affected, false otherwise.
     */
    public function execute(): bool
    {
        $this->builder();

        if ($this->query != null) {
            $this->pdo->query($this->query);
            foreach ($this->binds as $bind) {
                if (!$bind->hasBind()) {
                    $this->pdo->bind($bind->getBind(), $bind->getValue());
                }
            }

            $this->pdo->execute();

            return $this->pdo->rowCount() > 0;
        }

        return false;
    }
}
