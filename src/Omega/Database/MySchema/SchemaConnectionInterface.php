<?php

declare(strict_types=1);

namespace Omega\Database\MySchema;

use Omega\Database\ConnectionInterface;

interface SchemaConnectionInterface extends ConnectionInterface
{
    public function getDatabase(): string;
}
