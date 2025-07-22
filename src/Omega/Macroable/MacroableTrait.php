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

use Closure;
use Omega\Macroable\Exceptions\MacroNotFoundException;

use function array_key_exists;

/**
 * Trait MacroableTrait
 *
 * Provides dynamic method registration and invocation capabilities through macros.
 *
 * This trait enables a class to register custom methods (macros) at runtime using the `macro()`
 * method, and to dynamically handle method calls via `__call()` and `__callStatic()`.
 *
 * Macros are especially useful for extending class behavior in a modular and decoupled way,
 * without modifying the original class definition.
 *
 * Note: This trait is intended to be used in conjunction with the `MacroableInterface`.
 *
 * @category  Omega
 * @package   Macroable
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
trait MacroableTrait
{
    /** @var string[] List of registered macro. */
    protected static array $macros = [];

    /**
     * Register a macro method.
     *
     * This allows you to define a new method at runtime, associated with the given name.
     *
     * @param string   $macroName The name of the macro (i.e., the method to be registered).
     * @param callable $callBack  A callable that will be invoked when the macro is called.
     * @return void
     */
    public static function macro(string $macroName, callable $callBack): void
    {
        self::$macros[$macroName] = $callBack;
    }

    /**
     * Dynamically handle static method calls to registered macros.
     *
     * @param string             $method     The name of the method being called.
     * @param array<int, string> $parameters The parameters passed to the method.
     * @return mixed
     * @throws MacroNotFoundException If the macro has not been registered.
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        if (!array_key_exists($method, self::$macros)) {
            throw new MacroNotFoundException($method);
        }

        /** @var Closure $macro */
        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo(null, static::class);
        }

        return $macro(...$parameters);
    }

    /**
     * Dynamically handle instance method calls to registered macros.
     *
     * @param string             $method     The name of the method being called.
     * @param array<int, string> $parameters The parameters passed to the method.
     * @return mixed
     * @throws MacroNotFoundException If the macro has not been registered.
     */
    public function __call(string $method, array $parameters): mixed
    {
        if (!array_key_exists($method, self::$macros)) {
            throw new MacroNotFoundException($method);
        }

        /** @var Closure $macro */
        $macro = static::$macros[$method];

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return $macro(...$parameters);
    }

    /**
     * Determine if a macro is registered.
     *
     * @param string $macroName The name of the macro.
     * @return bool True if the macro is registered; otherwise, false.
     */
    public static function hasMacro(string $macroName): bool
    {
        return array_key_exists($macroName, self::$macros);
    }

    /**
     * Remove all registered macros.
     *
     * This is useful for resetting the state during testing or runtime reconfiguration.
     *
     * @return void
     */
    public static function resetMacro(): void
    {
        self::$macros = [];
    }
}
