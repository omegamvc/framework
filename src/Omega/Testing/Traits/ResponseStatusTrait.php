<?php

declare(strict_types=1);

namespace Omega\Testing\Traits;

use Omega\Http\Response;

trait ResponseStatusTrait
{
    public function assertOk(): void
    {
        $this->assertStatusCode(Response::HTTP_OK, 'Response code must return ok');
    }

    public function assertCreated(): void
    {
        $this->assertStatusCode(Response::HTTP_CREATED, 'Response code must return create');
    }

    public function assertNoContent(): void
    {
        $this->assertStatusCode(Response::HTTP_NO_CONTENT, 'Response code must return no content');
    }

    public function assertBadRequest(): void
    {
        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, 'Response code must return Bad Request');
    }

    public function assertUnauthorized(): void
    {
        $this->assertStatusCode(Response::HTTP_UNAUTHORIZED, 'Response code must return Unauthorized');
    }

    public function assertForbidden(): void
    {
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, 'Response code must return Forbidden');
    }

    public function assertNotFound(): void
    {
        $this->assertStatusCode(Response::HTTP_NOT_FOUND, 'Response code must return Not Found');
    }

    public function assertNotAllowed(): void
    {
        $this->assertStatusCode(Response::HTTP_METHOD_NOT_ALLOWED, 'Response code must return Not Allowed');
    }
}
