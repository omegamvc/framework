<?php

declare(strict_types=1);

return [
    'Cache'    => 'Omega\Cache\CacheManager',
    'Config'   => 'Omega\Config\ConfigRepository',
    'DB'       => 'Omega\Database\MyQuery',
    'Hash'     => 'Omega\Security\Hashing\HashManager',
    'PDO'      => 'Omega\Database\MyPDO',
    'Schedule' => 'Omega\Cron\Schedule',
    'Schema'   => 'Omega\Database\MySchema',
];
