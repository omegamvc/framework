<?php

use Tests\Support\Bootstrap\TestVendorServiceProvider;

return [
    'omegamvc/nexus' => [
        'providers' => [
            TestVendorServiceProvider::class,
        ],
    ],
];
