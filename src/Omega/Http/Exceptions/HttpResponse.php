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

namespace Omega\Http\Exceptions;

use Omega\Http\Response;
use RuntimeException;
use Throwable;

/**
 * Class HttpResponse
 *
 * A special exception that carries a pre-built HTTP Response object.
 * When thrown, it is intercepted by the exception handler and returned as-is.
 *
 * This allows interrupting the application flow and immediately returning a response,
 * for example in middleware or in controller-level logic.
 *
 * @category   Omega
 * @package    Http
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class HttpResponse extends RuntimeException implements HttpExceptionInterface
{
    /**
     * @var Response The response to return directly to the client.
     */
    protected Response $response;

    /**
     * Create a new HttpResponse exception instance.
     *
     * @param Response           $response The response object to be returned.
     * @param string|null        $message  Optional message, defaults to class name.
     * @param int                $code     Optional error code.
     * @param Throwable|null     $previous Optional previous throwable for chaining.
     * @return void
     */
    public function __construct(
        Response $response,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->response = $response;

        parent::__construct($message ?? 'HTTP Response Exception', $code, $previous);
    }

    /**
     * Get the response associated with this exception.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
