<?php

/**
 * Part of Omega - Tests\Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Support\Bootstrap;

use Omega\Collection\Collection;
use Omega\Support\Facades\Facade;

/**
 * A test facade class for the Collection component.
 *
 * This class is used to expose the `Collection` class methods
 * through a static interface during testing. It allows checking
 * behavior of macroable or extended Collection functionalities.
 *
 * @category   Omega\Tests
 * @package    Support
 * @subpackage Bootstrap
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 *
 * @method static bool has(string $key) Checks if the collection contains a specific key.
 */
class TestCollectionFacade extends Facade
{
    /**
     * Returns the class name of the underlying implementation.
     *
     * @return string
     */
    public static function getAccessor(): string
    {
        return Collection::class;
    }
}
