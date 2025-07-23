<?php

/**
 * Part of Omega - Tests\Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Http;

use Omega\Http\Request;
use Omega\Http\Response;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Support class for kernel termination test.
 *
 * This class provides a simple implementation of a termination handler
 * that outputs the request URL and response content when the kernel terminates.
 *
 * @category  Omega\Tests
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversNothing]
class TestKernelTerminate
{
    /**
     * Handles the termination process by outputting request and response data.
     *
     * @param Request  $request  The HTTP request instance.
     * @param Response $response The HTTP response instance.
     * @return void
     */
    public function terminate(Request $request, Response $response): void
    {
        echo $request->getUrl();
        echo $response->getContent();
    }
}
