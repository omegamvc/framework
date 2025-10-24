<?php

declare(strict_types=1);

namespace Omega\Database\Schema;

use Omega\Database\Connection;

class SchemaConnection extends Connection implements SchemaConnectionInterface
{
    /** @var string */
    private string $database;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $configs)
    {
        $dsnConfig      = $this->setConfigs($configs);
        $this->database = $configs['database'] ?? $configs['database_name'];
        $dsn            = $this->getDsn($dsnConfig);
        $this->pdo      = $this->createConnection($dsn, $dsnConfig, $dsnConfig['options']);
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * {@inheritDoc}
     */
    protected function setConfigs(array $configs): array
    {
        return $this->configs = [
            'driver'   => $configs['driver'] ?? 'mysql',
            'host'     => $configs['host'] ?? null,
            'database' => null,
            'port'     => $configs['port'] ?? null,
            'charset'  => $configs['charset'] ?? null,
            'username' => $configs['user'] ?? $configs['username'] ?? null,
            'password' => $configs['password'] ?? null,
            'options'  => $configs['options'] ?? $this->option,
        ];
    }
}
