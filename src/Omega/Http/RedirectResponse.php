<?php

/**
 * Part of Omega - Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Http;

use function htmlspecialchars;
use function sprintf;

use const ENT_QUOTES;

/**
 * Represents an HTTP redirect response.
 *
 * Automatically sets the `Location` header and generates an HTML body
 * with a refresh meta tag for compatibility with older user agents.
 *
 * Extends the base Response class to handle 3xx redirects (default 302).
 *
 * @category  Omega
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class RedirectResponse extends Response
{
    /**
     * Create a new redirect response instance.
     *
     * @param string               $url          The URL to redirect to.
     * @param int                  $responseCode The HTTP status code (typically 302, 301, etc.).
     * @param array<string,string> $headers      Optional headers to include with the response.
     * @return void
     */
    public function __construct(string $url, int $responseCode = 302, array $headers = [])
    {
        parent::__construct('', $responseCode, $headers);

        $this->setTarget($url);
    }

    /**
     * Set the target URL for the redirect.
     *
     * This method updates the response content with an HTML document
     * and sets the `Location` header accordingly.
     *
     * @param string $url The destination URL for the redirect.
     * @return void
     */
    public function setTarget(string $url): void
    {
        $this->setContent(sprintf('<html><head><meta charset="UTF-8" /><meta http-equiv="refresh" content="0;url=\'%1$s\'" /><title>Redirecting to %1$s</title></head><body>Redirecting to <a href="%1$s">%1$s</a>.</body></html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));
        $this->setHeaders([
            'Location' => $url,
        ]);
    }
}
