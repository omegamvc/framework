<?php

declare(strict_types=1);

namespace Tests\Router;

use PHPUnit\Framework\TestCase;
use Omega\Http\Request;
use Omega\Router\RouteDispatcher;
use Omega\Router\Router;

class RouteControllerTest extends TestCase
{
    protected $backup;

    protected function tearDown(): void
    {
        Router::Reset();
    }

    public function dispatcher(string $url, string $method): false|string
    {
        $request  = new Request($url, [], [], [], [], [], [], $method);
        $dispatch = new RouteDispatcher($request, Router::getRoutesRaw());

        $call = $dispatch->run(
            // found
            function ($callable, $param) {
                if (is_array($callable)) {
                    [$class, $method] = $callable;

                    return call_user_func_array([new $class(), $method], $param);
                }

                return call_user_func($callable, $param);
            },
            // not found
            function ($path) {
                echo 'not found';
            },
            // method not allowed
            function ($path, $method) {
                echo 'not allowed';
            },
        );

        ob_start();
        call_user_func_array($call['callable'], $call['params']);

        return ob_get_clean();
    }

    /** @return void */
    public function testItCanRouteUsingResourceController()
    {
        Router::resource('/', RouteClassController::class);

        $res = $this->dispatcher('/', 'get');
        $this->assertEquals('works', $res);

        $res = $this->dispatcher('/create', 'get');
        $this->assertEquals('works create', $res);

        $res = $this->dispatcher('/', 'post');
        $this->assertEquals('works store', $res);

        $res = $this->dispatcher('/12', 'get');
        $this->assertEquals('works show', $res);

        $res = $this->dispatcher('/12/edit', 'get');
        $this->assertEquals('works edit', $res);

        $res = $this->dispatcher('/12', 'put');
        $this->assertEquals('works update', $res);

        $res = $this->dispatcher('/12', 'delete');
        $this->assertEquals('works destroy', $res);
    }

    /** @return void */
    public function testItCanRouteUsingResourceControllerWithCostumeOnly()
    {
        Router::resource('/', RouteClassController::class, [
            'only' => ['index'],
        ]);

        $res = $this->dispatcher('/', 'get');
        $this->assertEquals('works', $res);

        $res = $this->dispatcher('/create', 'get');
        $this->assertEquals('not found', $res);

        $res = $this->dispatcher('/', 'post');
        $this->assertEquals('not allowed', $res);

        $res = $this->dispatcher('/12', 'get');
        $this->assertEquals('not found', $res);

        $res = $this->dispatcher('/12/edit', 'get');
        $this->assertEquals('not found', $res);

        $res = $this->dispatcher('/12', 'put');
        $this->assertEquals('not found', $res);

        $res = $this->dispatcher('/12', 'delete');
        $this->assertEquals('not found', $res);
    }

    /** @return void */
    public function testItCanRouteUsingResourceControllerWithCostumeOnlyUsingChain()
    {
        Router::resource('/', RouteClassController::class)
            ->only(['index']);

        $res = $this->dispatcher('/', 'get');
        $this->assertEquals('works', $res);

        $res = $this->dispatcher('/create', 'get');
        $this->assertEquals('not found', $res);

        $res = $this->dispatcher('/', 'post');
        $this->assertEquals('not allowed', $res);

        $res = $this->dispatcher('/12', 'get');
        $this->assertEquals('not found', $res);

        $res = $this->dispatcher('/12/edit', 'get');
        $this->assertEquals('not found', $res);

        $res = $this->dispatcher('/12', 'put');
        $this->assertEquals('not found', $res);

        $res = $this->dispatcher('/12', 'delete');
        $this->assertEquals('not found', $res);
    }

    /** @return void */
    public function testItCanRouteUsingResourceControllerWithCostumeExcept()
    {
        Router::resource('/', RouteClassController::class, [
            'except' => ['store'],
        ]);

        $res = $this->dispatcher('/', 'get');
        $this->assertEquals('works', $res);

        $res = $this->dispatcher('/', 'post');
        $this->assertEquals('not allowed', $res);

        $res = $this->dispatcher('/create', 'get');
        $this->assertEquals('works create', $res);

        $res = $this->dispatcher('/12', 'get');
        $this->assertEquals('works show', $res);

        $res = $this->dispatcher('/12/edit', 'get');
        $this->assertEquals('works edit', $res);

        $res = $this->dispatcher('/12', 'put');
        $this->assertEquals('works update', $res);

        $res = $this->dispatcher('/12', 'delete');
        $this->assertEquals('works destroy', $res);
    }

    /** @return void */
    public function testItCanRouteUsingResourceControllerWithCostumeExceptUsingChain()
    {
        Router::resource('/', RouteClassController::class)
            ->except(['store']);

        $res = $this->dispatcher('/', 'get');
        $this->assertEquals('works', $res);

        $res = $this->dispatcher('/', 'post');
        $this->assertEquals('not allowed', $res);

        $res = $this->dispatcher('/create', 'get');
        $this->assertEquals('works create', $res);

        $res = $this->dispatcher('/12', 'get');
        $this->assertEquals('works show', $res);

        $res = $this->dispatcher('/12/edit', 'get');
        $this->assertEquals('works edit', $res);

        $res = $this->dispatcher('/12', 'put');
        $this->assertEquals('works update', $res);

        $res = $this->dispatcher('/12', 'delete');
        $this->assertEquals('works destroy', $res);
    }

    /** @return void */
    public function testItRouteResourceHaveName(): void
    {
        Router::resource('/', RouteClassController::class);

        $this->assertTrue(Router::has('Tests\Router\RouteClassController.index'));
        $this->assertTrue(Router::has('Tests\Router\RouteClassController.create'));
        $this->assertTrue(Router::has('Tests\Router\RouteClassController.store'));
        $this->assertTrue(Router::has('Tests\Router\RouteClassController.show'));
        $this->assertTrue(Router::has('Tests\Router\RouteClassController.edit'));
        $this->assertTrue(Router::has('Tests\Router\RouteClassController.destroy'));
    }

    /** @return void */
    public function testItRouteResourceHaveNameWithPrefix()
    {
        Router::name('test.')->group(function () {
            Router::resource('/', RouteClassController::class);
        });

        $this->assertTrue(Router::has('test.Tests\Router\RouteClassController.index'));
        $this->assertTrue(Router::has('test.Tests\Router\RouteClassController.create'));
        $this->assertTrue(Router::has('test.Tests\Router\RouteClassController.store'));
        $this->assertTrue(Router::has('test.Tests\Router\RouteClassController.show'));
        $this->assertTrue(Router::has('test.Tests\Router\RouteClassController.edit'));
        $this->assertTrue(Router::has('test.Tests\Router\RouteClassController.destroy'));
    }

    /** @return void */
    public function testItCanModifyResourceMap()
    {
        Router::resource('/', EmptyRouteClassController::class, [
            'map' => ['index' => 'api'],
        ]);
        $res = $this->dispatcher('/', 'get');
        $this->assertEquals('works api', $res);
    }

    /** @return void */
    public function testItCanModifyResourceMapUsingChain()
    {
        Router::resource('/', EmptyRouteClassController::class)
            ->map(['index' => 'api', 'create' => 'api_create']);
        $res = $this->dispatcher('/', 'get');
        $this->assertEquals('works api', $res);
        $res = $this->dispatcher('/create', 'get');
        $this->assertEquals('works api_create', $res);
    }

    /** @return void */
    public function testItCanCostumeResourceWhenMissing()
    {
        Router::resource('/', EmptyRouteClassController::class)
            ->missing(function () {
                echo '404';
            });
        $res = $this->dispatcher('/', 'get');
        $this->assertEquals('404', $res);
    }

    /** @return void */
    public function testItCanCostumeResourceWhenMissingUsingSetup()
    {
        Router::resource('/', EmptyRouteClassController::class, [
            'missing' => function () {
                echo '404';
            },
        ]);
        $res = $this->dispatcher('/', 'get');
        $this->assertEquals('404', $res);
    }
}
