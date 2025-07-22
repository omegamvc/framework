<?php

/**
 * Part of Omega - Collection Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Collection\Exceptions;

use RuntimeException;

/**
 * Exception thrown when attempting to modify an immutable collection.
 *
 * This exception indicates that the collection instance is read-only
 * and cannot be altered once created.
 *
 * @category   Omega
 * @package    Collection
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class CollectionImmutableException extends RuntimeException implements CollectionExceptionInterface
{
    /**
     * Constructs the CollectionImmutableException.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('Cannot modify an immutable collection.');
    }
}
