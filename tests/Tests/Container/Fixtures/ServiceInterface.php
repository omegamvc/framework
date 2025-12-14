<?php

/**
 * Part of Omega - Tests\Container Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Container\Fixtures;

use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Marker interface for service resolution tests.
 *
 * Used to verify interface-to-concrete bindings.
 *
 * @category   Tests
 * @package    Container
 * @subpackage Fixtures
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
#[CoversNothing]
interface ServiceInterface
{
}
