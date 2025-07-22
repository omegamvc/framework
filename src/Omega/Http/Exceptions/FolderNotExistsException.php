<?php

/**
 * Part of Omega - Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Http\Exceptions;

use InvalidArgumentException;

use function sprintf;

/**
 * Class FolderNotExistsException
 *
 * Thrown when a specified folder path does not exist in the filesystem.
 *
 * @category   Omega
 * @package    Http
 * @subpackage Exceptions
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class FolderNotExistsException extends InvalidArgumentException implements HttpExceptionInterface
{
    /**
     * Creates a new exception instance.
     *
     * @param string $folderLocation The expected folder location that does not exist.
     *
     * @return void
     */
    public function __construct(string $folderLocation)
    {
        parent::__construct(sprintf('Folder location not exists `%s`', $folderLocation));
    }
}
