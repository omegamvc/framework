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

namespace Omega\Filesystem\Factory;

/*
 * @use
 */
use Omega\Filesystem\Adapter\FilesystemAdapterInterface;
use Omega\Filesystem\Adapter\Amazon\AwsS3;
use Omega\Filesystem\Adapter\Amazon\AsyncAwsS3;
use Omega\Filesystem\Adapter\Ftp\Ftp;
use Omega\Filesystem\Adapter\Local\Local;
use Omega\Filesystem\Exception\UnsupportedAdapterException;

/**
 * Filesystem factory class.
 *
 * The `FilesystemFactory` class is responsible for registering and creating session
 * drivers based on configurations. It acts as a factory for different session
 * drivers and provides a flexible way to connect to various session systems.
 *
 * @category    Omega
 * @package     Filesystem
 * @subpackage  Factory
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
class FilesystemFactory implements FilesystemFactoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @return mixed Return the created object or value. The return type is flexible, allowing for any type to be
     *               returned, depending on the implementation.
     */
    public function create(?array $config = null): FilesystemAdapterInterface
    {
        if (!isset($config['type'])) {
            throw new UnsupportedAdapterException(
                'Type is not defined.'
            );
        }

        return match ($config['type']) {
            's3'      => new AwsS3($config),
            'asyncs3' => new AsyncAwsS3($config),
            'ftp'     => new Ftp($config),
            'local'   => new Local($config['path']),
            default   => throw new UnsupportedAdapterException('Unrecognised type.')
        };
    }
}