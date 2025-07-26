<?php

/**
 * Part of Omega - Cache Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\Adapter;

use DateMalformedStringException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Omega\Cache\AbstractCacheItemPool;
use Omega\Cache\Exceptions\InvalidArgumentException;
use Omega\Cache\Exceptions\PhpRuntimeException;
use Omega\Cache\Item\CacheItemInterface;
use Omega\Cache\Item\HasExpirationDateInterface;
use Omega\Cache\Item\Item;

use function fclose;
use function file_put_contents;
use function fopen;
use function hash;
use function is_dir;
use function is_file;
use function is_writable;
use function json_decode;
use function json_encode;
use function mkdir;
use function pathinfo;
use function sprintf;
use function stream_get_contents;
use function time;
use function unlink;

/**
 * File-based cache item pool implementation.
 *
 * This class provides a file-based storage mechanism for cache items, ensuring
 * that cache data is stored persistently in a file system directory.
 *
 * Supported options:
 * - path              : The path for cache files.
 *
 * @category   Omega
 * @package    Cache
 * @subpackage Adapter
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class File extends AbstractCacheItemPool
{
    /** @var string Holds the cache path. */
    private string $path;

    /**
     * Constructor.
     *
     * Initializes the file-based cache system, ensuring that the required options
     * are set and validating the cache directory.
     *
     * @param array<string, mixed> $options An associative array of configuration options.
     * @return void
     * @throws PhpRuntimeException if the specified path is either not a directory or is not writable.
     * @throws InvalidArgumentException If the 'path' option is not provided.
     */
    public function __construct(array $options)
    {
        if (!array_key_exists('path', $options) || !is_string($options['path']) || $options['path'] === '') {
            throw new InvalidArgumentException('The path option must be set and be a non-empty string.');
        }

        $this->path = $options['path'];

        if (!is_dir($this->path)) {
            throw new PhpRuntimeException("The specified file path is not a directory.");
        }

        if (!is_writable($this->path)) {
            throw new PhpRuntimeException("The specified file path is not writable.");
        }

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->path)
            ),
            '/\.json$/i'
        );

        /** @var RecursiveDirectoryIterator $file */
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                @unlink($file->getRealPath());
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DateMalformedStringException if the provided date string is malformed or not in a valid format.
     * @throws PhpRuntimeException if the cache file:
     *     - cannot be opened,
     *     - cannot be read,
     *     - contains invalid JSON,
     *     - or cannot be deleted when expired.
     */
    public function getItem(string $key): CacheItemInterface
    {
        if (!$this->hasItem($key)) {
            return new Item($key);
        }

        $resource = @fopen($this->fetchStreamUri($key), 'rb');

        if (!$resource) {
            throw new PhpRuntimeException(sprintf('Unable to fetch cache entry for %s. Cannot open the resource.', $key));
        }

        $data = stream_get_contents($resource);
        fclose($resource);

        if ($data === false) {
            throw new PhpRuntimeException(
                sprintf(
                    'Unable to read cache entry for %s. The file might be corrupted or unreadable.',
                    $key
                )
            );
        }

        $item = new Item($key);
        $dataArray = json_decode($data, true);

        if ($dataArray === null) {
            throw new PhpRuntimeException(
                sprintf(
                    'Unable to decode cache entry for %s. The file might be corrupted.',
                    $key
                )
            );
        }

        if (is_array($dataArray) && isset($dataArray['expires'])) {
            // Check expiration date
            if (time() > $dataArray['expires']) {
                if (!$this->deleteItem($key)) {
                    throw new PhpRuntimeException(sprintf('Unable to clean expired cache entry for %s.', $key));
                }

                return $item;
            }
        }

        if (is_array($dataArray) && isset($dataArray['value'])) {
            $item->set($dataArray['value']);
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem(string $key): bool
    {
        if ($this->hasItem($key)) {
            return @unlink($this->fetchStreamUri($key));
        }

        // If the item doesn't exist, no error
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        $fileName = $this->fetchStreamUri($item->getKey());
        $filePath = pathinfo($fileName, PATHINFO_DIRNAME);

        if (!is_dir($filePath)) {
            mkdir($filePath, 0770, true);
        }

        // Saving in JSON format instead of serialization
        $data = [
            'value'   => $item->get(),
            'expires' => $item instanceof HasExpirationDateInterface
                ? time() + $this->convertItemExpiryToSeconds($item)
                : null
        ];

        return (bool) file_put_contents(
            $fileName,
            json_encode($data)  // Use json_encode() instead of serialize()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem(string $key): bool
    {
        return is_file($this->fetchStreamUri($key));
    }

    /**
     * Determines if the File cache implementation is supported.
     *
     * This method verifies whether the necessary file system functionality
     * is available for the cache system to operate correctly.
     *
     * @return bool Returns true if the file-based cache system is supported, false otherwise.
     */
    public static function isSupported(): bool
    {
        return true;
    }

    /**
     * Generates the full stream URI for a cache entry.
     *
     * Constructs the file path for storing the cache entry based on the provided key.
     *
     * @param string $key The cache item identifier.
     * @return string Returns the full stream URI for the cache entry.
     * @throws PhpRuntimeException If the cache path is invalid.
     */
    public function fetchStreamUri(string $key): string
    {
        //$filePath = $this->options['path'];
        //$this->checkFilePath($filePath);

        return sprintf(
            '%s/%s.json',
            rtrim($this->path, '/'),
            hash('md5', $key)
        );
    }
}
