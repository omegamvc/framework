<?php

/**
 * Part of Omega - Support Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Support;

use Closure;
use Omega\Http\Upload\UploadFile;
use Omega\Http\Request;
use Omega\Container\Provider\AbstractServiceProvider;
use Omega\Validator\Validator;

/**
 * Service provider that extends the Request object with custom macros.
 *
 * This provider registers two useful macros on the Request class:
 * - `validate`: Instantiates a Validator with request input, using optional rules and filters.
 * - `upload`: Wraps an uploaded file into an UploadFile instance for easier file handling.
 *
 * These macros allow cleaner and more expressive usage in controllers and middleware,
 * making request validation and file uploads more convenient throughout the application.
 *
 * This provider is registered automatically by the Application during bootstrapping.
 *
 * @category  Omega
 * @package   Support
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class RequestMacroServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        Request::macro(
            'validate',
            fn (?Closure $rule = null, ?Closure $filter = null) => Validator::make($this->{'all'}(), $rule, $filter)
        );

        Request::macro(
            'upload',
            function ($file_name) {
                $files = $this->{'getFile'}();

                return new UploadFile($files[$file_name]);
            }
        );
    }
}
