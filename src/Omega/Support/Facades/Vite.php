<?php

/**
 * Part of Omega - Facades Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support\Facades;

/**
 * Facade for the Vite service.
 *
 * This facade provides a static interface to the underlying `Vite` instance
 * resolved from the application container. It allows convenient static-style
 * calls while still relying on dependency injection and the container under the hood.
 *
 * Usage of this facade does not create a global state; the underlying instance
 * is still managed by the container and may be swapped, mocked, or replaced
 * for testing or customization purposes.
 *
 * @category   Omega
 * @package    Support
 * @subpackges Facades
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 *
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
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor(): string
    {
        return 'vite.gets';
    }
}
