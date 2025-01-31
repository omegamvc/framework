<?php

/**
 * Part of Omega - Application Package
 * php version 8.2
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

 declare(strict_types=1);

namespace Omega\Application;

use Omega\Application\Exception\SingletonException;

use function get_called_class;

/**
 * Application instance trait.
 * 
 * @category  Omega
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.
 */
trait ApplicationInstanceTrait
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
        $getCalledClass = get_called_class();

        if (!isset(self::$instances[$getCalledClass])) {
            self::$instances[$getCalledClass] = new $getCalledClass($basePath);
        }

        return self::$instances[$getCalledClass];
    }

    /**
     * Clone method.
     *
     * This method is overridden to prevent cloning of the singleton instance.
     * Cloning would create a second instance, which violates the Singleton pattern.
     *
     * @return void
     *
     * @throws SingletonException If an attempt to clone the singleton is made.
     */
    public function __clone(): void
    {
        throw new SingletonException(
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
     *
     * @throws SingletonException If an attempt at deserialization is made.
     */
    public function __wakeup(): void
    {
        throw new SingletonException(
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
     *
     * @throws SingletonException If an attempt at serialization is made.
     */
    public function __sleep(): array
    {
        throw new SingletonException(
            'You can not serialize a singleton.'
        );
    }
}