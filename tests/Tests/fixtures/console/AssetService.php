<?php

declare(strict_types=1);

class AssetService extends Service
{
    public function Test(array $request): array
    {
        return [
            'status'  => 'ok',
            'data'    => null,
            'error'   => false,
            'headers' => ['HTTP/1.1 200 Oke']
        ];
    }
}
