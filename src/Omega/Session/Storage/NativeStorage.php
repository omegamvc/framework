<?php

/**
 * Part of Omega -  Session Package.
 * php version 8.3
 *
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */

declare(strict_types=1);

namespace Omega\Session\Storage;

use LogicException;

use function array_keys;
use function extension_loaded;
use function session_start;
use function session_status;
use function str_starts_with;

/**
 * Native driver class.
 *
 * The `NativeStorage` provides native session storage using PHP's built-in session handling.
 *
 * @category   Omega
 * @package    Session
 * @subpackage Storage
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class NativeStorage extends AbstractStorage
{
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
     * @return void
     */
    public function __construct(
        private readonly array $config
    ) {
        if (! extension_loaded('session')) {
            throw new LogicException('PHP extension "session" is required. Load it and reload the page.');
        }

        if (PHP_SESSION_ACTIVE !== session_status() && ! session_start()) {
            session_start();
        }

        $this->prefix = isset($this->config['prefix']) && is_string($this->config['prefix'])
            ? $this->config['prefix']
            : '';
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return isset($_SESSION["{$this->prefix}{$key}"]);
    }

    /**
     * {@inheritdoc}
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
     */
    public function put(string $key, mixed $value): static
    {
        $_SESSION["{$this->prefix}{$key}"] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function forget(string $key): static
    {
        unset($_SESSION["{$this->prefix}{$key}"]);

        return $this;
    }

    /**
     * {@inheritdoc}
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
