<?php

/**
 * Part of Omega - Tests\Config Package
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Config\Source;

use Omega\Config\Source\AbstractFile;

use function compact;
use function trim;

/**
 * Test double for file-based configuration sources.
 *
 * This class is used in unit tests to simulate the behavior of reading and parsing
 * content from a configuration file. It wraps the file content inside an associative
 * array using a fixed key, making assertions predictable and isolated.
 *
 * @category   Tests
 * @package    Config
 * @subpackage Source
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class TestFileConfigurationSource extends AbstractFile
{
    /**
     * Retrieves the trimmed content of the test configuration file.
     *
     * @return array An associative array with a single key 'content' containing the file content.
     */
    public function fetch(): array
    {
        $content = trim($this->fetchContent());

        return compact('content');
    }
}
