<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Application\Application;
use RuntimeException;

use function array_key_exists;

abstract class AbstractFacade implements FacadeInterface
{
    /**
     * Application accessor.
     */
    protected static ?Application $app = null;

    /**
     * Instance accessor.
     *
     * @var array<string, mixed>
     */
    protected static array $instance = [];

    /**
     * Set Accessor.
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        static::$app = $app;
    }

    /**
     * Set facade instance.
     */
    public static function setFacadeBase(?Application $app = null): void
    {
        static::$app = $app;
    }

    /**
     * Get accessor from application.
     *
     * @return string
     */
    abstract public static function getFacadeAccessor(): string;

    /**
     * Facade.
     *
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected static function getFacade(): mixed
    {
        return static::getFacadeBase(static::getFacadeAccessor());
    }

    /**
     * Facade.
     *
     * @param string|class-string $name Entry name or a class name
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected static function getFacadeBase(string $name): mixed
    {
        if (array_key_exists($name, static::$instance)) {
            return static::$instance[$name];
        }

        return static::$instance[$name] = static::$app->make($name);
    }

    /**
     * Clear all the instances.
     */
    public static function flushInstance(): void
    {
        static::$instance = [];
    }

    /**
     * Call static from accessor.
     *
     * @param string $name
     * @param array<int, mixed> $arguments
     * @return mixed
     * @throws RuntimeException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        $instance = static::getFacade();

        if (!$instance) {
            throw new FacadeObjectNotSetException(
                static::class
            );
        }

        return $instance->$name(...$arguments);
    }
}
