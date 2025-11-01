<?php

/**
 * Part of Omega - Tests\Support\Facades Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Support\Facades\Sample;

use Omega\Collection\Collection;
use Omega\Support\Facades\AbstractFacade;

/**
 * Test facade used to validate the facade resolution mechanism.
 *
 * This class simply points the facade accessor to the Collection class,
 * allowing assertions to be made on static call forwarding behavior and
 * instance caching in the underlying AbstractFacade implementation.
 *
 * @category   Tests
 * @package    Support
 * @subpackage Facades\Sample
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
 * @method static bool has(string $key)
 */
final class FacadesTestClass extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor(): string
    {
        return Collection::class;
    }
}
