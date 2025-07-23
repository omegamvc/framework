<?php

declare(strict_types=1);

namespace App\Models;

use Omega\Database\Model\Model\Model;

class User extends Model
{
    protected string $table_name  = 'users';
    protected string $primary_key = 'id';
}
