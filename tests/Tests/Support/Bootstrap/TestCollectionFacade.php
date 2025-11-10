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

use Omega\Collection\Collection;
use Omega\Support\Facades\AbstractFacade;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Class TestCollectionFacade
 *
 * This is a minimal facade used exclusively for testing the facade
 * registration process. It resolves to a Collection instance from
 * the application's container, allowing verification that facades
 * are correctly bound and accessible after bootstrap.
 *
 * @category   Tests
 * @package    Support
 * @subpackage Bootstrap
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @method static bool has(string $key)
 */
#[CoversClass(AbstractFacade::class)]
final class TestCollectionFacade extends AbstractFacade
{
    /**
     * {@inheritDoc}
     */
    public static function getFacadeAccessor(): string
    {
        return Collection::class;
    }
}
