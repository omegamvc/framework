<?php

/**
 * Part of Omega - Tests\Collection Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Collection;

use PHPUnit\Framework\TestCase;

use function Omega\Collection\data_get;

/**
 * Class DataGetTest
 *
 * This test class verifies the behavior of the `data_get()` helper function, which allows
 * retrieving deeply nested values from arrays using dot notation keys.
 *
 * The test cases cover the following scenarios:
 * - Accessing nested array values using standard dot notation (e.g., "one.two.three").
 * - Providing a default value when the key does not exist.
 * - Using wildcards in keys to retrieve multiple matches across array branches.
 * - Handling numeric keys for flat arrays.
 *
 * The test dataset includes a variety of nested and multi-level array structures
 * to ensure the robustness and correctness of the `data_get()` implementation.
 *
 * @category  Omega\Tests
 * @package   Collection
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class DataGetTest extends TestCase
{
    /** @var array|\array[][] Holds an array of data for test. */
    private array $array = [
        'awesome'   => ['lang'  => ['go', 'rust', 'php', 'python', 'js']],
        'fav'       => ['lang'  => ['rust', 'php' ]],
        'dont_know' => ['lang_' => ['back_end' => ['erlang', 'h-lang']]],
        'one'       => ['two'   => ['three' => ['four' => ['five' => 6]]]],
    ];

    /**
     * Test it can find item using fot keys.
     *
     * @return void
     */
    public function testItCanFindItemUsingDotKeys(): void
    {
        $this->assertEquals(6, data_get($this->array, 'one.two.three.four.five'));
    }

    /**
     * Test it can find item using dot keys but dont exists.
     *
     * @return void
     */
    public function testItCanFindItemUsingDotKeysButDontExist(): void
    {
        $this->assertEquals('six', data_get($this->array, '1.2.3.4.5', 'six'));
    }

    /**
     * Test it can find item using dot keys with wildcard.
     *
     * @return void
     */
    public function testItCanFindItemUsingDotKeysWithWildcard(): void
    {
        $this->assertEquals([
            ['go', 'rust', 'php', 'python', 'js'],
            ['rust', 'php'],
        ], data_get($this->array, '*.lang'));
    }

    /**
     * Test it can get keys as integer.
     *
     * @return void
     */
    public function testItCanGetKeysAsInteger(): void
    {
        $array5 = ['foo', 'bar', 'baz'];
        $this->assertEquals('bar', data_get($array5, 1));
        $this->assertNull(data_get($array5, 3));
        $this->assertEquals('qux', data_get($array5, 3, 'qux'));
    }
}
