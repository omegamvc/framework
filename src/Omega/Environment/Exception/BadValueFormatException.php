<?php

/**
 * Part of Omega - Environment Package.
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Environment\Exception;

use InvalidArgumentException as PhpInvalidArgumentException;

class BadValueFormatException extends PhpInvalidArgumentException implements
    InvalidRuntimeConfigurationExceptionInterface
{
    public function __construct(string $key)
    {
        parent::__construct("Invalid value format for key: {$key}. Expected a string.");
    }
}
