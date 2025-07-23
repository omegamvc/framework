<?php

/**
 * Part of Omega - Testing Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Testing;

use Omega\Http\Response;
use Omega\Testing\Traits\ResponseStatusTrait;
use PHPUnit\Framework\Assert;

/**
 * TestResponse is a wrapper for the HTTP Response object used in tests.
 *
 * Provides utility methods to assert the response content and status code,
 * making it easier to write expressive and readable HTTP assertions in tests.
 *
 * @category  Omega
 * @package   Testing
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class TestResponse
{
    use ResponseStatusTrait;

    /**
     * Create a new TestResponse instance.
     *
     * @param Response $response The original response to be tested.
     */
    public function __construct(protected Response $response)
    {
    }

    /**
     * Get the raw content of the HTTP response as a string.
     *
     * @return string The response body content.
     */
    public function getContent(): string
    {
        return $this->response->getContent();
    }

    /**
     * Assert that the given text is present in the response content.
     *
     * @param string $text    The expected string to be found.
     * @param string $message Optional message to display on failure.
     * @return void
     */
    public function assertSee(string $text, string $message = ''): void
    {
        Assert::assertStringContainsString($text, $this->response->getContent(), $message);
    }

    /**
     * Assert that the response has the given HTTP status code.
     *
     * @param int    $code    The expected HTTP status code.
     * @param string $message Optional message to display on failure.
     * @return void
     */
    public function assertStatusCode(int $code, string $message = ''): void
    {
        Assert::assertSame($code, $this->response->getStatusCode(), $message);
    }
}
