<?php

/**
 * Part of Omega - Tests\Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Http;

use Exception;
use Omega\Http\HeaderCollection;
use Omega\Text\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * Unit tests for the HeaderCollection class.
 *
 * This test suite verifies the proper generation of HTTP header strings,
 * setting headers by key-value pairs or raw strings, and error handling
 * for invalid header formats. It also utilizes the Str helper class
 * for string containment checks.
 *
 * @category  Omega\Tests
 * @package   Http
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(HeaderCollection::class)]
#[CoversClass(Str::class)]
class ResponseCollectionTest extends TestCase
{
    /**
     * Test it can generate header to header string.
     *
     * @return void
     */
    public function testItCanGenerateHeaderToHeaderString(): void
    {
        $header = new HeaderCollection([
            'Host'       => 'test.test',
            'Accept'     => 'text/html',
            'Connection' => 'keep-alive',
        ]);

        $this->assertTrue(Str::contains((string) $header, 'Host: test.test'));
        $this->assertTrue(Str::contains((string) $header, 'Accept: text/htm'));
        $this->assertTrue(Str::contains((string) $header, 'Connection: keep-alive'));
    }

    /**
     * Test it can generate header using set ith value.
     *
     * @return void
     */
    public function testItCanGenerateHeaderUsingSetWithValue(): void
    {
        $header = new HeaderCollection([]);
        $header->set('Host', 'test.test');
        $header->set('Accept', 'text/html');
        $header->set('Connection', 'keep-alive');

        $this->assertTrue(Str::contains((string) $header, 'Host: test.test'));
        $this->assertTrue(Str::contains((string) $header, 'Accept: text/htm'));
        $this->assertTrue(Str::contains((string) $header, 'Connection: keep-alive'));
    }

    /**
     * Test it can generate header using set with key only.
     *
     * @retunr void
     * @throws Exception
     */
    public function testItCanGenerateHeaderUsingSetWithKeyOnly(): void
    {
        $header = new HeaderCollection([]);
        $header->setRaw('Host: test.test');
        $header->setRaw('Accept: text/html');
        $header->setRaw('Connection: keep-alive');

        $this->assertTrue(Str::contains((string) $header, 'Host: test.test'));
        $this->assertTrue(Str::contains((string) $header, 'Accept: text/htm'));
        $this->assertTrue(Str::contains((string) $header, 'Connection: keep-alive'));
    }

    /**
     * Test it can generate header using set with key only but throw error.
     *
     * @return void
     * @throws Exception
     * @throws Throwable
     */
    public function testItCanGenerateHeaderUsingSetWithKeyOnlyButThrowError(): void
    {
        $header  = new HeaderCollection([]);
        $message = '';
        try {
            $header->setRaw('Host=test.test');
        } catch (Throwable $th) {
            $message = $th->getMessage();
        }

        $this->assertEquals('Invalid header structure Host=test.test.', $message);
    }
}
