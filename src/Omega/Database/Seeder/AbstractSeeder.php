<?php

declare(strict_types=1);

namespace Omega\Database\Seeder;

use Omega\Database\ConnectionInterface;
use Omega\Database\Query\Insert;

abstract class AbstractSeeder implements SeederInterface
{
    /**
     * AbstractSeeder class constructor.
     *
     * @param ConnectionInterface $pdo
     * @return void
     */
    public function __construct(protected ConnectionInterface $pdo)
    {
    }

    /**
     * {@inherotdoc}
     */
    public function call(string $className): void
    {
        $class = new $className($this->pdo);
        $class->run();
    }

    /**
     * {@inherotdoc}
     */
    public function create(string $tableName): Insert
    {
        return new Insert($tableName, $this->pdo);
    }

    /**
     * {@inherotdoc}
     */
    abstract public function run(): void;
}
