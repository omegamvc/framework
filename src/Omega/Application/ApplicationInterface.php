<?php

/**
 * Part of Omega - Application Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Application;

/**
 * Application Interface Class.
 *
 * @category  Omega
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */
interface ApplicationInterface
{
    /**
     * The Omega framework version.
     *
     * @var string Holds the Omega framework version.
     */
    public const string VERSION = '1.0.0';

    /**
     * Get the version of the application.
     *
     * @return string Return the version of the application.
     */
    public function getVersion(): string;

    /**
     * Get or check the current application environment.
     *
     * This method allows you to either retrieve the current environment or check if the application is running in a
     * specific environment.
     *
     * @param  string|string[] ...$environments One or more environment names to check against the current environment.
     *
     * If no parameters are provided, the method returns the current environment as a string.
     * If one or more environment names are provided, the method returns `true` if the current environment matches any
     * of the provided names; otherwise, it returns `false`.
     *
     * @return string|bool The current environment as a string if no parameters are provided; `true` or `false` if
     *                     checking against the provided environments.
     */
    public function environment(string|array ...$environments): string|bool;

    /**
     * Sets the default timezone for the application.
     *
     * This method reads the timezone from the application configuration and sets it
     * as the default timezone for all date and time operations within the application.
     *
     * @return $this Returns a reference to the current object for method chaining.
     */
    public function setCurrentTimeZone(): self;

    /**
     * Gets the current date and time formatted according to the application timezone.
     *
     * @return string The formatted current date and time.
     */
    public function getCurrentTimeZone(): string;
}