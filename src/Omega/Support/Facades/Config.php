<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Config\ConfigRepository;

/**
 * Facade for the Configuration Repository.
 *
 * Provides static access to the configuration system, allowing retrieval,
 * setting, and manipulation of configuration values stored in the application.
 *
 * Magic methods map to the ConfigRepository methods, enabling simple static
 * interaction with the underlying configuration service.
 *
 * @category   Omega
 * @package    Support
 * @subpackage Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @method static bool has(string $key) Check if a configuration key exists.
 * @method static mixed get(string $key, mixed $default = null) Retrieve a configuration value.
 * @method static void set(string $key, mixed $value) Set a configuration value.
 * @method static void push(string $key, mixed $value) Append a value to a configuration array.
 * @method static array<string, mixed> toArray() Get all configuration items as an array.
 */
class Config extends Facade
{
    /**
     * Get the service accessor key for the configuration service.
     *
     * This key is used by the base Facade class to resolve the ConfigRepository
     * instance from the application container.
     *
     * @return string The configuration service accessor key.
     */
    protected static function getAccessor(): string
    {
        return ConfigRepository::class;
    }
}
