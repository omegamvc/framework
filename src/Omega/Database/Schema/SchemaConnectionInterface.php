<?php

declare(strict_types=1);

namespace Omega\Database\Schema;

use Omega\Database\ConnectionInterface;

interface SchemaConnectionInterface extends ConnectionInterface
{
    public function getDatabase(): string;
}
