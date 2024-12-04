<?php

/**
 * Part of Omega -  Session Package.
 *
 * @see       https://omegamvc.github.io
 *
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/*
 * @declare
 */
declare(strict_types=1);

/**
 * @namespace
 */

namespace Omega\Session\Storage;

/*
 * @use
 */
use function array_keys;
use function extension_loaded;
use function session_start;
use function session_status;
use function str_starts_with;
use LogicException;

/**
 * Native driver class.
 *
 * The `NativeStorage` provides native session storage using PHP's built-in session handling.
 *
 * @category    Omega
 * @package     Session
 * @subpackage  Storage
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class NativeStorage extends AbstractStorage
{
    /**
     * Configuration array.
     *
     * @var array<string, mixed> Holds an array of configuration parameters.
     */
    private readonly array $config;

    /**
     * The session prefix.
     *
     * @var string Holds the session prefix.
     */
    private readonly string $prefix;

    /**
     * NativeStorage constructor.
     *
     * @param array<string, mixed> $config Holds an array of configuration parameters.
     *
     * @return void
     */
    public function __construct(array $config)
    {
        if (! extension_loaded('session')) {
            throw new LogicException('PHP extension "session" is required. Load it and reload the page.');
        }

        if (PHP_SESSION_ACTIVE !== session_status() && ! session_start()) {
            session_start();
        }

        $this->config = $config;
        //$this->prefix = $this->config[ 'prefix' ] ?? '';
        $this->prefix = isset($this->config['prefix']) && is_string($this->config['prefix'])
            ? $this->config['prefix']
            : '';
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key The session key.
     *
     * @return bool Return true if the session value exists.
     */
    public function has(string $key): bool
    {
        return isset($_SESSION["{$this->prefix}{$key}"]);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key     The session key.
     * @param mixed  $default The default value to return if the key is not found.
     *
     * @return mixed Return the session value or the default value if the key is not found.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($_SESSION["{$this->prefix}{$key}"])) {
            return $_SESSION["{$this->prefix}{$key}"];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key   The session key.
     * @param mixed  $value The session value.
     *
     * @return $this
     */
    public function put(string $key, mixed $value): static
    {
        $_SESSION["{$this->prefix}{$key}"] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $key The session key.
     *
     * @return $this
     */
    public function forget(string $key): static
    {
        unset($_SESSION["{$this->prefix}{$key}"]);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function flush(): static
    {
        foreach (array_keys($_SESSION) as $key) {
            if (str_starts_with($key, $this->prefix)) {
                unset($_SESSION[$key]);
            }
        }

        return $this;
    }
}
