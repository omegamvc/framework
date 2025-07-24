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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Config\Exceptions\FileReadException;

use function compact;
use function dirname;

/**
 * Unit tests for the AbstractFile configuration source.
 *
 * Verifies that file contents are correctly read,
 * and appropriate exceptions are thrown when files are unreadable or missing.
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
#[CoversClass(FileReadException::class)]
class AbstractFileTest extends TestCase
{
    /**
     * est it should fetch file content.
     * @return void
     */
    public function testItShouldFetchFileContent(): void
    {
        $configurationSource = new TestFileConfigurationSource(
            dirname(__DIR__, 2) . '/fixtures1/config/content.txt'
        );
        $content = 'content';

        $this->assertEquals(compact('content'), $configurationSource->fetch());
    }

    /**
     * Test it should throw if file not readable.
     *
     * @return void
     */
    public function testItShouldThrowIfFileNotReadable(): void
    {
        $this->expectException(FileReadException::class);

        (new TestFileConfigurationSource(
            dirname(__DIR__, 2) . '/fixtures1/config/not-found.txt')
        )->fetch();
    }
}
