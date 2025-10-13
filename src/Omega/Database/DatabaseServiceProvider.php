<?php

declare(strict_types=1);

namespace Omega\Database;

use Omega\Container\Definition\Exceptions\InvalidDefinitionException;
use Omega\Container\Exceptions\DependencyException;
use Omega\Container\Exceptions\NotFoundException;
use Omega\Container\Provider\AbstractServiceProvider;

class DatabaseServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws InvalidDefinitionException
     */
    public function boot(): void
    {
        $configs     = $this->app->get('config');
        $default     = $configs['db.default'];
        $connections = $configs['db.connections'];
        $dsn         = $connections[$default];

        $this->app->set('dsn.default', $default);
        $this->app->set('dsn.connections', $connections);
        $this->app->set('dsn.sql', $dsn);

        $this->app->set(
            MyPDO::class,
            fn () => new MyPDO($this->app->get('dsn.sql'))
        );

        $this->app->set(
            MySchema\MyPDO::class,
            fn () => new MySchema\MyPDO($this->app->get('dsn.sql'))
        );

        $this->app->set(
            'MyQuery',
            fn () => new MyQuery($this->app->get(MyPDO::class))
        );

        $this->app->set(
            'MySchema',
            fn () => new MySchema($this->app->get(MySchema\MyPDO::class), $this->app->get('dsn.sql')['database'])
        );

        $this->app->set(
            DatabaseManager::class,
            fn () => new DatabaseManager($this->app->get('dsn.connections'))
                ->setDefaultConnection($this->app->get(MyPDO::class))
        );
    }
}
