<?php

use Tests\Support\Bootstrap\TestVendorServiceProvider;

return [
    'omegamvc/firstpackage' => [
        'providers' => [
            TestVendorServiceProvider::class,
        ],
    ],
];
