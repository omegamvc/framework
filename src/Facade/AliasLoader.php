<?php

/**
 * Part of Omega - Support Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Facade;

/**
 * Class AliasLoader.
 *
 * The `AliasLoader` class is responsible for managing and loading class aliases
 * within the application. It simplifies the process of creating aliases for
 * classes, allowing developers to reference them with shorter or more meaningful
 * names. By utilizing the `load` method, all specified aliases can be registered
 * at once, enhancing code readability and maintainability.
 *
 * This class follows the singleton pattern by providing a static `getInstance`
 * method to instantiate the loader with a given set of aliases.
 *
 * @category   Omega
 * @package    Facade
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
final class AliasLoader
{
    /**
     * Create a new AliasLoader instance.
     *
     * @param array<string, string> $aliases Holds an associative array where the keys are the alias names
     *                                       and the values are the fully qualified class names.
     *
     * @return void
     */
    public function __construct(protected array $aliases)
    {
    }

    /**
     * Load the class aliases.
     *
     * This method registers all class aliases defined in the `aliases` array
     * by creating an alias for each class. It uses the PHP built-in `class_alias`
     * function to establish the aliasing relationship.
     *
     * @return void
     */
    public function load(): void
    {
        foreach ($this->aliases as $alias => $class) {
            class_alias($class, $alias);
        }
    }

    /**
     * Get an instance of AliasLoader.
     *
     * This static method returns a new instance of the `AliasLoader` class,
     * initialized with the provided aliases. This method allows for convenient
     * creation of an `AliasLoader` instance without the need to instantiate it
     * directly.
     *
     * @param array<string, string> $aliases Holds a associative array where the keys are the alias names
     *                                       and the values are the fully qualified class names.
     * @return AliasLoader Returns a new instance of AliasLoader.
     */
    public static function getInstance(array $aliases): AliasLoader
    {
        return new AliasLoader($aliases);
    }
}
