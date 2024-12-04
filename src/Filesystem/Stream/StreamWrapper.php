<?php

/**
 * Part of Omega - Filesystem Package.
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

namespace Omega\Filesystem\Stream;

/*
 * @use
 */
use function array_merge;
use function in_array;
use function parse_url;
use function sprintf;
use function stream_get_wrappers;
use function stream_wrapper_register;
use function stream_wrapper_unregister;
use function substr;
use Omega\Filesystem\FilesystemMap;
use InvalidArgumentException;
use RuntimeException;

/**
 * Stream wrapper class for the Gaufrette filesystems.
 *
 * @category    Omega
 * @package     Filesystem
 * @subpackage  Stream
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class StreamWrapper
{
    /** @var FilesystemMap The map of filesystems used by the stream wrapper. */
    private static FilesystemMap $filesystemMap;

    /** @var StreamInterface The current stream being manipulated by this wrapper. */
    private StreamInterface $stream;

    /**
     * Defines the filesystem map.
     *
     * @param FilesystemMap $map
     */
    public static function setFilesystemMap(FilesystemMap $map): void
    {
        self::$filesystemMap = $map;
    }

    /**
     * Returns the filesystem map.
     *
     * @return FilesystemMap $map
     */
    public static function getFilesystemMap(): FilesystemMap
    {
        if (null === self::$filesystemMap) {
            self::$filesystemMap = self::createFilesystemMap();
        }

        return self::$filesystemMap;
    }

    /**
     * Registers the stream wrapper to handle the specified scheme.
     *
     * @param string $scheme Default is gaufrette
     */
    public static function register(string $scheme = 'omega'): void
    {
        self::streamWrapperUnregister($scheme);

        if (!self::streamWrapperRegister($scheme, __CLASS__)) {
            throw new RuntimeException(
                sprintf(
                    'Could not register stream wrapper class %s for scheme %s.',
                    __CLASS__,
                    $scheme
                )
            );
        }
    }

    /**
     * @return FilesystemMap
     */
    protected static function createFilesystemMap(): FilesystemMap
    {
        return new FilesystemMap();
    }

    /**
     * @param string $scheme - protocol scheme
     *
     * @return bool
     */
    protected static function streamWrapperUnregister(string $scheme): bool
    {
        if (in_array($scheme, stream_get_wrappers())) {
            return stream_wrapper_unregister($scheme);
        }

        return false;
    }

    /**
     * @param string $scheme    - protocol scheme
     * @param string $className
     *
     * @return bool
     */
    protected static function streamWrapperRegister(string $scheme, string $className): bool
    {
        return stream_wrapper_register($scheme, $className);
    }

    /**
     * Opens a stream to the specified path with the given mode.
     *
     * This method initializes a stream for the given path by creating it via
     * `createStream()` and then opening it in the specified mode using `createStreamMode()`.
     * The mode determines the type of access (e.g., read, write) that will be performed
     * on the stream.
     *
     * @param string $path The full path to the resource to be opened.
     * @param string $mode The mode for opening the stream (e.g., 'r', 'w', 'a').
     *
     * @return bool Returns true if the stream was successfully opened, or false on failure.
     */
    public function stream_open(string $path, string $mode): bool
    {
        $this->stream = $this->createStream($path);

        return $this->stream->open($this->createStreamMode($mode));
    }

    /**
     * @param int $bytes
     *
     * @return string|false
     */
    public function stream_read(int $bytes): string|false
    {
        return $this->stream->read($bytes);
    }

    /**
     * @param string $data
     *
     * @return int
     */
    public function stream_write(string $data): int
    {
        return $this->stream->write($data);
    }

    public function stream_close(): void
    {
        $this->stream->close();
    }

    /**
     * @return bool
     */
    public function stream_flush(): bool
    {
        return $this->stream->flush();
    }

    /**
     * @param int $offset
     * @param int $whence - one of values [SEEK_SET, SEEK_CUR, SEEK_END]
     *
     * @return bool
     */
    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        return $this->stream->seek($offset, $whence);
    }

    /**
     * @return int
     */
    public function stream_tell(): int
    {
        return $this->stream->tell();
    }

    /**
     * @return bool
     */
    public function stream_eof(): bool
    {
        return $this->stream->eof();
    }

    /**
     * @return array|false
     */
    public function stream_stat(): array|false
    {
        return $this->stream->stat();
    }

    /**
     * @param string $path
     * @param int    $flags
     *
     * @return array|false
     */
    public function url_stat(string $path, int $flags): array|false
    {
        $stream = $this->createStream($path);

        $suppressErrors = ($flags & STREAM_URL_STAT_QUIET) === STREAM_URL_STAT_QUIET;

        try {
            if (($flags & STREAM_URL_STAT_LINK) !== STREAM_URL_STAT_LINK) {
                $stream->open($this->createStreamMode('r+'));
            }
        } catch (RuntimeException $e) {
            if (!$suppressErrors) {
                throw $e;
            }
        }

        return $stream->stat();
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function unlink(string $path): bool
    {
        $stream = $this->createStream($path);

        try {
            $stream->open($this->createStreamMode('w+'));
        } catch (RuntimeException) {
            return false;
        }

        return $stream->unlink();
    }

    /**
     * @param int $castAs
     *
     * @return mixed
     */
    public function stream_cast(int $castAs): mixed
    {
        return $this->stream->cast($castAs);
    }

    /**
     * Creates a stream based on the provided path.
     *
     * Parses the provided path into components and uses the scheme, host, and path
     * to retrieve the corresponding filesystem from the filesystem map. It then creates
     * and returns a stream for the specified file.
     *
     * @param string $path The full path to the resource, including scheme and host.
     *
     * @return StreamInterface Returns a stream object associated with the given path.
     *
     * @throws InvalidArgumentException If the path is invalid or incomplete.
     */
    protected function createStream(string $path): StreamInterface
    {
        $parts = array_merge(
            [
                'scheme'   => null,
                'host'     => null,
                'path'     => null,
                'query'    => null,
                'fragment' => null,
            ],
            parse_url($path) ?: []
        );

        $domain = $parts['host'];
        $key    = !empty($parts['path']) ? substr($parts['path'], 1) : '';

        if (null !== $parts['query']) {
            $key .= '?' . $parts['query'];
        }

        if (null !== $parts['fragment']) {
            $key .= '#' . $parts['fragment'];
        }

        if (empty($domain) || empty($key)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The specified path (%s) is invalid.',
                    $path
                )
            );
        }

        return self::getFilesystemMap()->get($domain)->createStream($key);
    }

    /**
     * Creates a StreamMode object based on the provided mode string.
     *
     * The mode string is typically one of the standard PHP file modes (e.g., 'r', 'w', 'a').
     * This method wraps the mode in a `StreamMode` object that is used to interact
     * with the underlying stream.
     *
     * @param string $mode The mode for opening the stream (e.g., 'r', 'w', 'a').
     *
     * @return StreamMode Returns a `StreamMode` object that encapsulates the given mode.
     */
    protected function createStreamMode(string $mode): StreamMode
    {
        return new StreamMode($mode);
    }
}
