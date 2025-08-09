<?php

/**
 * Part of Omega - Config Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Config\Source;

/**
 * Defines the contract for configuration sources.
 *
 * Implementations of this interface provide a mechanism to retrieve
 * configuration data from various sources such as arrays, JSON files,
 * or XML files.
 *
 * @category   Omega
 * @package    Config
 * @subpackage Source
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
interface SourceInterface
{
    /**
     * Retrieves the configuration content from the source.
     *
     * @return array The configuration data as an associative array.
     */
    public function fetch(): array;
}