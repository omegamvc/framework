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

/**
 * Application Interface Class.
 *
 * @category  Omega
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
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
    public const VERSION = '1.0.0';

    /**
     * Get the singleton instance.
     *
     * This method returns the singleton instance of the class. If an instance
     * doesn't exist, it creates one and returns it.
     *
     * @param string|null $basePath Holds the Omega application base path or null.
     * @return static Return the singleton instance.
     */
    public static function getInstance(?string $basePath = null): static;

    /**
     * Get the version of the application.
     *
     * @return string Return the version of the application.
     */
    public function getVersion(): string;

    /**
     * Get the base path of the Omega installation.
     *
     * @param  string $path Holds the application path.
     * @return string Return the path of Omega installation.
     */
    public function getBasePath(string $path = ''): string;

    /**
     * Set the base path for Omega installation.
     *
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setBasePath(string $basePath): self;

    /**
     * Get the path to the bootstrap directory defined by the developer.
     *
     * @param  string $path Holds the custom bootstrap path defined by the developer.
     * @return string Return the path for bootstrap directory.
     */
    public function getBootstrapPath(string $path = ''): string;

    /**
     * Set bootstrap file directory path.
     *
     * @param  string $path Holds the application path.
     * @return $this
     */
    public function setBootstrapPath(string $path): self;

    /**
     * Get the path to the configuration directory defined by the developer.
     *
     * @param  string $path Holds the custom configuration path defined by the developer.
     * @return string Return the path for the configuration path.
     */
    public function getConfigPath(string $path = ''): string;

    /**
     * Set the configuration directory path.
     *
     * @param  string $path Holds the application path.
     * @return $this
     */
    public function setConfigPath(string $path): self;

    /**
     * Get the path to the database directory defined by the developer.
     *
     * @param  string $path Holds the custom database path defined by the developer.
     * @return string Return the path for the database files.
     */
    public function getDatabasePath(string $path = ''): string;

    /**
     * Set the database directory path.
     *
     * @param  string $path Holds the application path.
     * @return $this
     */
    public function setDatabasePath(string $path): self;

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
     * Get the path to the language directory defined by the developer.
     *
     * @param  string $path Holds the custom language path defined by the developer.
     * @return string Return the path to the language file directory.
     */
    public function getLangPath(string $path = ''): string;

    /**
     * Set the lang directory path.
     *
     * @param  string $path Holds the application path.
     * @return $this
     */
    public function setLangPath(string $path): self;

    /**
     * Get the path to the public/web directory defined by the developer.
     *
     * @param  string $path Holds the custom public/web path defined by the developer.
     * @return string Return the path to the public/web path directory.
     */
    public function getPublicPath(string $path = ''): string;

    /**
     * Set the public directory path.
     *
     * @param  string $path Holds the application path.
     * @return $this
     */
    public function setPublicPath(string $path): self;

    /**
     * Get the path to the resources directory.
     *
     * @param  string $path Holds the application resources path.
     * @return string Return the path to the resources path directory.
     */
    public function getResourcePath(string $path = ''): string;

    /**
     * Get the path to the storage directory.
     *
     * @param  string $path Holds the storage path.
     * @return string Return the path to the storage path directory.
     */
    public function getStoragePath(string $path = ''): string;

    /**
     * Set the storage directory path.
     *
     * @param  string $path Holds the application path.
     * @return $this
     */
    public function setStoragePath(string $path): self;

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
