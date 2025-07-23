<?php

declare(strict_types=1);

namespace Tests\Router;

class RouteClassController
{
    public function index(): void
    {
        echo 'works';
    }

    public function create(): void
    {
        echo 'works create';
    }

    public function store(): void
    {
        echo 'works store';
    }

    public function show(): void
    {
        echo 'works show';
    }

    public function edit(): void
    {
        echo 'works edit';
    }

    public function update(): void
    {
        echo 'works update';
    }

    public function destroy(): void
    {
        echo 'works destroy';
    }
}
