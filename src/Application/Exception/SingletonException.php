<?php

/**
 * Part of Omega - Application Package
 * php version 8.2
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace Omega\Application\Exception;

use Exception;

/**
 * Singleton exception.
 *
 * The `SingletonException` is thrown when there is an issue related to the
 * Singleton pattern implementation. It typically represents situations where
 * multiple instances of a Singleton class are attempted to be created or other
 * violations of the Singleton pattern.
 *
 * @category   Omega
 * @package    Application
 * @subpackage Exception
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    1.0.0
 */
class SingletonException extends Exception
{
}
