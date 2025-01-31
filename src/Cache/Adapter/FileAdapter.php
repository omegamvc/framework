<?php

/**
 * Part of Omega - Cache Package.
 * php version 8.2
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\Adapter;

use function glob;
use function json_decode;
use function json_encode;
use function file_get_contents;
use function file_put_contents;
use function is_int;
use function is_file;
use function sha1;
use function time;
use function unlink;

/**
 * File adapter class.
 *
 * The `FileAdapter` class implements a cache adapter that stores cached data in
 * files. It extends the AbstractCacheAdapter class and provides methods to read,
 * write, and manage cache data stored in files.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Adapter
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class FileAdapter extends AbstractCacheAdapter
{
    /**
     * FileAdapter class constructor.
     *
     * Initializes the FileAdapter with configuration options.
     *
     * @param array<string, mixed> $config Holds an array of configuration options.
     * @return void
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        $data = $this->cached[$key] = $this->read($key);

        if (is_array($data) && isset($data['expires']) && $data['expires'] > time()) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (is_array($this->cached[$key]) && isset($this->cached[$key]['value'])) {
            return $this->cached[$key]['value'];
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, mixed $value, ?int $seconds = null): static
    {
        if (!is_int($seconds)) {
            $seconds = $this->config['seconds'];
        }

        $data = $this->cached[$key] = [
            'value'   => $value,
            'expires' => time() + $seconds,
        ];

        return $this->write($key, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function forget(string $key): static
    {
        unset($this->cached[$key]);

        $path = $this->getPath($key);

        if (is_file($path)) {
            unlink($path);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): static
    {
        $this->cached = [];
        $files = glob($this->getBase() . DIRECTORY_SEPARATOR . '*.json');

        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        return $this;
    }

    /**
     * Get the cache path for a specific key.
     *
     * @param string $key Holds the cache key.
     * @return string Return the cache path for the specified key.
     */
    private function getPath(string $key): string
    {
        return $this->getBase() . DIRECTORY_SEPARATOR . sha1($key) . '.json';
    }

    /**
     * Get the cache base directory.
     *
     * @return string Return the base directory for storing cache files.
     */
    private function getBase(): string
    {
        /** @phpstan-ignore-next-line */
        return $this->config['path'];
    }

    /**
     * Read the cache data from a file.
     *
     * @param string $key Holds the cache key.
     * @return mixed|array Return the cached data, or an empty array if not found.
     */
    private function read(string $key): mixed
    {
        $path = $this->getPath($key);

        if (!is_file($path)) {
            return [];
        }

        $content = file_get_contents($path);

        if ($content === false) {
            return [];
        }

        return json_decode($content, true) ?: [];
    }

    /**
     * Write the cache data to a file.
     *
     * @param string $key   Holds the cache key.
     * @param mixed  $value Holds the value to cache.
     * @return $this Return the cache adapter instance.
     */
    private function write(string $key, mixed $value): static
    {
        file_put_contents($this->getPath($key), json_encode($value));

        return $this;
    }
}
