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

use Omega\Security\Hashing\HashInterface;
use Omega\Security\Hashing\HashManager;

/**
 * Facade for the Hash service.
 *
 * This facade provides a static interface to the underlying `Hash` instance
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
 * @method static HashManager   setDefaultDriver(HashInterface $driver)
 * @method static HashManager   setDriver(string $driver_name, HashInterface $driver)
 * @method static HashInterface driver(?string $driver = null)
 * @method static array         info(string $hashed_value)
 * @method static string        make(string $value, array $options = [])
 * @method static bool          verify(string $value, string $hashed_value, array $options = [])
 * @method static bool          isValidAlgorithm(string $hash)
 *
 * @see HashManager
 */
final class Hash extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor(): string
    {
        return HashManager::class;
    }
}
