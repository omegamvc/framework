<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

use Omega\Config\ConfigRepository;

/**
 * @method static bool                 has(string $key)
 * @method static mixed                get(string $key, mixed $default = null)
 * @method static void                 set(string $key, mixed $value)
 * @method static void                 push(string $key, mixed $value)
 * @method static array<string, mixed> getAll()
 *
 * @see ConfigRepository
 */
final class Config extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return ConfigRepository::class;
    }
}
