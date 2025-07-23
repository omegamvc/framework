<?php

declare(strict_types=1);

namespace Tests\Router;

use PHPUnit\Framework\TestCase;
use Omega\Router\Route;
use Omega\Router\RouteDispatcher;
use Omega\Router\Router;

class RouteDispatchTest extends TestCase
{
    protected function tearDown(): void
    {
        Router::Reset();
    }

    private function routes(): array
    {
        return [
            new Route([
                'method'     => 'GET',
                'expression' => '/',
                'function'   => fn() => true,
            ]),
        ];
    }

    /** @return void */
    public function testItCanDispatchAndCall()
    {
        $dispatcher = RouteDispatcher::dispatchFrom('/', 'GET', $this->routes());

        $dispatch = $dispatcher->run(
            fn($callable, $params) => call_user_func_array($callable, $params),
            fn($path) => 'not found - ',
            fn($path, $method) => 'method not allowed - - ',
        );

        $result = call_user_func_array($dispatch['callable'], $dispatch['params']);

        $this->assertEquals(true, $result);
    }

    /** @return void */
    public function testItCanDispatchAndRunFound()
    {
        $dispatcher = RouteDispatcher::dispatchFrom('/', 'GET', $this->routes());

        $dispatch = $dispatcher->run(
            fn() => 'found',
            fn($path) => 'not found - ',
            fn($path, $method) => 'method not allowed - - ',
        );

        $result = call_user_func_array($dispatch['callable'], $dispatch['params']);

        $this->assertEquals('found', $result);
    }

    /** @return void */
    public function testItCanDispatchAndRunNotFound()
    {
        $dispatcher = RouteDispatcher::dispatchFrom('/not-found', 'GET', $this->routes());

        $dispatch = $dispatcher->run(
            fn() => 'found',
            fn($path) => 'not found - ',
            fn($path, $method) => 'method not allowed - - ',
        );

        $result = call_user_func_array($dispatch['callable'], $dispatch['params']);

        $this->assertEquals('not found - ', $result);
    }

    /** @return void */
    public function testItCanDispatchAndRunMethodNotAllowed()
    {
        $dispatcher = RouteDispatcher::dispatchFrom('/', 'POST', $this->routes());

        $dispatch = $dispatcher->run(
            fn() => 'found',
            fn($path) => 'not found - ',
            fn($path, $method) => 'method not allowed - - ',
        );

        $result = call_user_func_array($dispatch['callable'], $dispatch['params']);

        $this->assertEquals('method not allowed - - ', $result);
    }
}
