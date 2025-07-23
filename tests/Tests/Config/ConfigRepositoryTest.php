<?php

/**
 * Part of Omega - Tests\Config Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Config;

use Omega\Config\ConfigRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the ConfigRepository class.
 *
 * This test class verifies the correct behavior of the ConfigRepository,
 * including basic operations such as getting, setting, checking, and pushing values,
 * as well as support for ArrayAccess functionality.
 *
 * @category  Omega\Tests
 * @package   Config
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(ConfigRepository::class)]
class ConfigRepositoryTest extends TestCase
{
    /**
     * Test it can perform repository.
     *
     * @return void
     */
    public function testItCanPerformRepository(): void
    {
        $env = [
            'envi'  => 'test',
            'num'   => 1,
            'allow' => true,
            'array' => ['omegamvc', 'php'],
        ];

        $config = new ConfigRepository($env);
        // get
        $this->assertEquals('test', $config->get('envi', 'local'));
        // set
        $config->set('envi', 'local');
        $this->assertEquals('local', (fn () => $this->{'config'}['envi'])->call($config));
        // has
        $this->assertTrue($config->has('num'));
        // push
        $config->push('array', 'library');
        $this->assertEquals(['omegamvc', 'php', 'library'], (fn () => $this->{'config'}['array'])->call($config));
    }

    /**
     * Test it can perform repository using array access.
     *
     * @return void
     */
    public function testItCanPerformRepositoryUsingArrayAccess(): void
    {
        $env = [
            'envi'  => 'test',
            'num'   => 1,
            'allow' => true,
        ];

        $config = new ConfigRepository($env);

        // get
        $this->assertEquals('test', $config['envi']);
        // set
        $config['envi'] = 'local';
        $this->assertEquals('local', (fn () => $this->{'config'}['envi'])->call($config));
        // has
        $this->assertTrue(isset($config['num']));
        // unset
        unset($config['allow']);
        $this->assertNull((fn () => $this->{'config'}['allow'])->call($config));
    }
}
