<?php

declare(strict_types=1);

namespace Omega\Http\Exceptions;

use Omega\Http\Response;
use RuntimeException;

class HttpResponseException extends RuntimeException
{
    /**
     * Creates a Responser Exception.
     */
    public function __construct(protected Response $response)
    {
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
