<?php

declare(strict_types=1);

namespace Tests\Router;

class EmptyRouteClassController
{
    public function api(): void
    {
        echo 'works api';
    }

    public function api_create(): void
    {
        echo 'works api_create';
    }
}
