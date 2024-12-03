<?php

/**
 * Part of Omega CMS - Container Package.
 * php version 8.3
 *
 * @link      https://omegacms.github.io
 * @author    Adriano Giovannini <omegacms@outlook.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Container\Exception;

use InvalidArgumentException;

/**
 * No entry was found in the container.
 *
 * The `KeyNotFoundException` class extends `InvalidArgumentException` and implements
 * the `NotFoundExceptionInterface`. It represents an exception that occurs when no entry
 * is found in the container.
 *
 * @category   Omega
 * @package    Container
 * @subpackage Exception
 * @link       https://omegacms.github.io
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class KeyNotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
}
