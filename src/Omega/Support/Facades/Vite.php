<?php

declare(strict_types=1);

namespace Omega\Support\Facades;

/**
 * @method static \Omega\Support\Vite   manifestName(string $manifestName)
 * @method static void                  flush()
 * @method static string                manifest()
 * @method static array                 loader()
 * @method static string                getManifest(string $resourceName)
 * @method static array<string, string> getsManifest(string[] $resourceNames)
 * @method static array                 getManifestImports(string[] $resources)
 * @method static string                get(string $resourceName)
 * @method static array<string, string> gets(string[] $resourceNames)
 * @method static bool                  isRunningHRM()
 * @method static string                getHmrUrl()
 * @method static string                getHmrScript()
 * @method static int                   cacheTime()
 * @method static int                   manifestTime()
 * @method static string                getPreloadTags(string[] $entryPoints)
 * @method static string getTags(string[] $entryPoints, array<string|int, string|bool|int|null> $attributes = null)
 * @method static string getCustomTags(array $entryPoints,array<string|int, string|bool|int|null> $defaultAttributes=[])
 *
 * @see \Omega\Support\Vite
 */
final class Vite extends AbstractFacade
{
    public static function getFacadeAccessor(): string
    {
        return 'vite.gets';
    }
}
