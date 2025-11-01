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

use Omega\Database\Schema\Create;
use Omega\Database\Schema\Drop;
use Omega\Database\Schema\Table\Alter;
use Omega\Database\Schema\Table\Create as TableCreate;
use Omega\Database\Schema\Table\Raw;
use Omega\Database\Schema\Table\Truncate;

/**
 * Facade for the Schema service.
 *
 * This facade provides a static interface to the underlying `Schema` instance
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
 * @method static Create      create()
 * @method static Drop        drop()
 * @method static Truncate    refresh(string $table_name)
 * @method static TableCreate table(string $table_name, callable $blueprint)
 * @method static Alter       alter(string $table_name, callable $blueprint)
 * @method static Raw         raw(string $raw)
 *
 * @see Schema
 */
final class Schema extends AbstractFacade
{
    /**
     * {@inheritdoc}
     */
    public static function getFacadeAccessor(): string
    {
        return 'Schema';
    }
}
