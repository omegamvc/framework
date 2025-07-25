<?php

/**
 * Part of Omega - Environment Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Environment\Exceptions;

class UnexpectedDirectoryException extends AbstractInvalidConfigurationException implements
    InvalidSourceDataExceptionInterface
{
    public function __construct(string $path)
    {
        parent::__construct("Expected a file, but found a directory: {$path}");
    }
}
