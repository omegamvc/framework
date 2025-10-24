<?php

declare(strict_types=1);

namespace Omega\Database\Seeder;

use Omega\Database\Query\Insert;

interface SeederInterface
{
    /**
     * Call the migration class.
     *
     * @param class-string $className Holds the class name to call.
     * @return void
     */
    public function call(string $className): void;

    /**
     * Create database seeder.
     *
     * @param string $tableName Holds the name of the table to insert.
     * @return Insert Return an object of Insert.
     */
    public function create(string $tableName): Insert;

    /**
     * Run seeder.
     *
     * @return void
     */
    public function run(): void;
}
