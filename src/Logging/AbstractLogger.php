<?php

/**
 * Part of Omega - Logging Package.
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

namespace Omega\Logging;

/**
 * Class AbstractLogger.
 *
 * This abstract class serves as a base implementation of the LoggerInterface. It provides common logging functionality
 * by including the LoggerTrait, which can be reused by concrete logger classes. Classes extending this abstract logger
 * must implement specific logging behavior as defined in the LoggerInterface.
 *
 * @category    Omega
 * @package     Logging
 *
 * @see        https://omegamvc.github.io
 *
 * @author      Adriano Giovannini <agisoftt@gmail.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegamvc.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 *
 * @version     1.0.0
 */
abstract class AbstractLogger implements LoggerInterface
{
    use LoggerTrait;
}