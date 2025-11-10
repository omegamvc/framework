<?php

/**
 * Part of Omega - Tests\Support\Bootstrap Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Support\Bootstrap;

use Omega\Container\Provider\AbstractServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class TestRegisterServiceProvider
 *
 * A minimal service provider used only for testing provider registration.
 * This provider does not register or boot any services. Its purpose is simply
 * to confirm that the application correctly stores and marks providers as
 * booted during the bootstrap phase.
 *
 * @category   Tests
 * @package    Support
 * @subpackage Bootstrap
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(AbstractServiceProvider::class)]
class TestRegisterServiceProvider extends AbstractServiceProvider
{
}
