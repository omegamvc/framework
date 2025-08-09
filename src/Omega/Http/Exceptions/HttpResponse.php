<?php

declare(strict_types=1);

namespace Omega\Http\Exceptions;

use Omega\Http\Response;

class HttpResponse extends \RuntimeException
{
    protected Response $response;

    /**
     * Creates a Responser Exception.
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
