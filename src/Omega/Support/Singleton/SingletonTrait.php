<?php

/**
 * Part of Omega - Application Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Singleton;

use Omega\Support\Singleton\Exceptions\ImmutableStateException;
use Omega\Support\Singleton\Exceptions\UndefinedSingletonClassException;

use function class_exists;
use function debug_backtrace;
use function is_string;

use const DEBUG_BACKTRACE_IGNORE_ARGS;

/**
 * Application singleton trait.
 *
 * @category  System
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
trait SingletonTrait
{
    /**
     * Singleton instance.
     *
     * @var static[] Holds the singleton instances.
     */
    private static array $instances;


    /**
     * Get the singleton instance.
     *
     * This method returns the singleton instance of the class. If an instance
     * doesn't exist, it creates one and returns it.
     *
     * @param string|null $basePath Holds the Omega application base path or null.
     * @return static Return the singleton instance.
     */
    public static function getInstance(?string $basePath = null): static
    {
        $getCalledClass = static::getSingletonClass();

        if (!isset(static::$instances[$getCalledClass])) {
            static::$instances[$getCalledClass] = new $getCalledClass($basePath);
        }

        return static::$instances[$getCalledClass];
    }

    /**
     * Determines the singleton class name.
     *
     * This method inspects the call stack to identify the class that invoked
     * the singleton. It ensures that the retrieved class name is valid and exists.
     *
     * @return string Return the fully qualified name of the singleton class.
     * @throws UndefinedSingletonClassException If a valid singleton class cannot be determined.
     */
    private static function getSingletonClass(): string
    {
        $backtrace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);

        foreach ($backtrace as $frame) {
            if (isset($frame['class']) && is_string($frame['class']) && class_exists($frame['class'])) {
                return $frame['class'];
            }
        }

        throw new UndefinedSingletonClassException(
            'Failed to determine a valid singleton class in getInstance().'
        );
    }

    /**
     * Clone method.
     *
     * This method is overridden to prevent cloning of the singleton instance.
     * Cloning would create a second instance, which violates the Singleton pattern.
     *
     * @return void
     * @throws ImmutableStateException If an attempt to clone the singleton is made.
     */
    public function __clone(): void
    {
        throw new ImmutableStateException(
            'You can not clone a singleton.'
        );
    }

    /**
     * Wakeup method.
     *
     * This method is overridden to prevent deserialization of the singleton instance.
     * Deserialization would create a second instance, which violates the Singleton pattern.
     *
     * @return void
     * @throws ImmutableStateException If an attempt at deserialization is made.
     */
    public function __wakeup(): void
    {
        throw new ImmutableStateException(
            'You can not deserialize a singleton.'
        );
    }

    /**
     * Sleep method.
     *
     * This method is overridden to prevent serialization of the singleton instance.
     * Serialization would create a second instance, which violates the Singleton pattern.
     *
     * @return array Return the names of private properties in parent classes.
     * @throws ImmutableStateException If an attempt at serialization is made.
     */
    public function __sleep(): array
    {
        throw new ImmutableStateException(
            'You can not serialize a singleton.'
        );
    }
}
