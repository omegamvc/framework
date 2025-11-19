<?php

/**
 * Part of Omega - Router Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Router\Attribute;

use Attribute;

/**
 * Attaches middleware to a class or method.
 *
 * Can be applied to a controller class or an individual method.
 * Specifies one or more middleware classes to be executed before
 * the route handler.
 *
 * Example usage:
 * ```php
 * #[Middleware(['AuthMiddleware', 'LogMiddleware'])]
 * class UserController { ... }
 *
 * #[Middleware(['ThrottleMiddleware'])]
 * public function create() { ... }
 * ```
 *
 * @category   Omega
 * @package    Router
 * @subpackage Attribute
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Middleware
{
    /**
     * List of middleware class names to apply.
     *
     * @var string[]
     */
    public array $middleware;

    /**
     * Initializes the Middleware attribute.
     *
     * @param string[] $middleware List of middleware class names.
     */
    public function __construct(array $middleware)
    {
        $this->middleware = $middleware;
    }
}
