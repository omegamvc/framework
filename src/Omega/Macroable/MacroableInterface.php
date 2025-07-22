<?php

/**
 * Part of Omega - Macroable Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Macroable;

use Omega\Macroable\Exceptions\MacroNotFoundException;

/**
 * Interface MacroableInterface
 *
 * Defines the contract for classes that support macro registration and dynamic method calls.
 * Macros allow users to register methods at runtime, enabling flexible and extensible behavior.
 *
 * Classes implementing this interface can dynamically handle both instance and static method
 * calls via registered closures.
 *
 * @category  Omega
 * @package   Macroable
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
interface MacroableInterface
{
    /**
     * Register a macro method.
     *
     * This allows you to define a new method at runtime, associated with the given name.
     *
     * @param string   $macroName The name of the macro (i.e., the method to be registered).
     * @param callable $callBack  A callable that will be invoked when the macro is called.
     * @return void
     */
    public static function macro(string $macroName, callable $callBack): void;

    /**
     * Dynamically handle static method calls to registered macros.
     *
     * @param string             $method     The name of the method being called.
     * @param array<int, string> $parameters The parameters passed to the method.
     * @return mixed
     * @throws MacroNotFoundException If the macro has not been registered.
     */
    public static function __callStatic(string $method, array $parameters): mixed;

    /**
     * Dynamically handle instance method calls to registered macros.
     *
     * @param string             $method     The name of the method being called.
     * @param array<int, string> $parameters The parameters passed to the method.
     * @return mixed
     * @throws MacroNotFoundException If the macro has not been registered.
     */
    public function __call(string $method, array $parameters): mixed;

    /**
     * Determine if a macro is registered.
     *
     * @param string $macroName The name of the macro.
     * @return bool True if the macro is registered; otherwise, false.
     */
    public static function hasMacro(string $macroName): bool;

    /**
     * Remove all registered macros.
     *
     * This is useful for resetting the state during testing or runtime reconfiguration.
     *
     * @return void
     */
    public static function resetMacro(): void;
}
