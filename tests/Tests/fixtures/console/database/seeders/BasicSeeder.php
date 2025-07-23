<?php

namespace Database\Seeders;

use Omega\Database\Seeder\Seeder;

use function Omega\Console\style;

class BasicSeeder extends Seeder
{
    public function run(): void
    {
        style('seed for basic seeder')->out(false);
    }
}
