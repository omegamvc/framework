<?php

declare(strict_types=1);

namespace Omega\Cron;

use Omega\Support\Facades\DB;

use function Omega\Time\now;
use function json_encode;

class Log implements InterpolateInterface
{
    public function interpolate(string $message, array $context = []): void
    {
        DB::table('cron')
            ->insert()
            ->values([
                'message'     => $message,
                'context'     => json_encode($context),
                'date_create' => now()->timestamp,
            ])
            ->execute();
    }
}
