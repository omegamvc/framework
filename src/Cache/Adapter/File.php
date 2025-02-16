<?php

/**
 * Part of Omega - Cache Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Cache\Adapter;

use DateMalformedStringException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RuntimeException as PhpRuntimeException;
use Omega\Cache\AbstractCacheItemPool;
use Omega\Cache\Exception\InvalidArgumentException;
use Omega\Cache\Exception\RuntimeException;
use Omega\Cache\Item\HasExpirationDateInterface;
use Omega\Cache\Item\Item;
use Omega\Cache\Item\CacheItemInterface;

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
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class File extends AbstractCacheItemPool
{
    /**
     * Constructor.
     *
     * Initializes the file-based cache system, ensuring that the required options
     * are set and validating the cache directory.
     *
     * @param mixed $options An associative array of configuration options.
     * @return void
     * @throws PhpRuntimeException If an unexpected runtime error occurs.
     * @throws InvalidArgumentException If the 'path' option is not provided.
     */
    public function __construct(mixed $options = [])
    {
        if (!isset($options['path'])) {
            throw new InvalidArgumentException(
                'The path option must be set.'
            );
        }

        $this->checkFilePath($options['path']);

        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $filePath = $this->options['path'];
        $this->checkFilePath($filePath);

        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($filePath)
            ),
            '/\.data$/i'
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
     * @throws DateMalformedStringException
     */
    public function getItem(string $key): CacheItemInterface
    {
        if (!$this->hasItem($key)) {
            return new Item($key);
        }

        $resource = @fopen($this->fetchStreamUri($key), 'rb');

        if (!$resource) {
            throw new RuntimeException(
                sprintf(
                    'Unable to fetch cache entry for %s. Cannot open the resource.',
                    $key
                )
            );
        }

        $data = stream_get_contents($resource);
        fclose($resource);

        $item = new Item($key);
        $dataArray = json_decode($data, true);  // Use json_decode() instead of unserialize()

        // Check expiration date
        if ($dataArray['expires'] !== null && time() > $dataArray['expires']) {
            if (!$this->deleteItem($key)) {
                throw new RuntimeException(
                    sprintf(
                        'Unable to clean expired cache entry for %s.',
                        $key
                    )
                );
            }

            return $item;
        }

        $item->set($dataArray['value']);

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
     * Validates the cache directory.
     *
     * Ensures that the provided file path exists, is a directory, and is writable.
     *
     * @param string $filePath The file path to validate.
     * @return bool Always returns true if the path is valid.
     * @throws RuntimeException If the file path does not exist or is not writable.
     */
    private function checkFilePath(string $filePath): bool
    {
        if (!is_dir($filePath)) {
            throw new RuntimeException(
                sprintf(
                    'The base cache path `%s` does not exist.',
                    $filePath
                )
            );
        }

        if (!is_writable($filePath)) {
            throw new RuntimeException(
                sprintf(
                    'The base cache path `%s` is not writable.',
                    $filePath
                )
            );
        }

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
    private function fetchStreamUri(string $key): string
    {
        $filePath = $this->options['path'];
        $this->checkFilePath($filePath);

        return sprintf(
            '%s/%s.json',
            rtrim($filePath, '/'), // Rimuove eventuali slash finali per sicurezza
            hash('md5', $key) // Nome del file basato su hash MD5
        );
    }
}
