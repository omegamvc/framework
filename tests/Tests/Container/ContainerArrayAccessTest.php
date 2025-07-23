<?php

/**
 * Part of Omega - Tests\Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Container;

use DI\DependencyException;
use DI\NotFoundException;
use Omega\Container\Container;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests the ArrayAccess implementation of the Container class.
 *
 * This test case ensures that the container supports array-like access,
 * allowing services to be set, retrieved, checked, and unset using array syntax.
 * It verifies that the behavior is consistent with the underlying set, get, has,
 * and unset methods of the container.
 *
 * @category  Omega\Tests
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Container::class)]
class ContainerArrayAccessTest extends TestCase
{
    /**
     * Test it can get has.
     *
     * @return void
     */
    public function testItCanGetHas(): void
    {
        $container = new Container();
        $container->set('test01', 1);

        $this->assertTrue(isset($container['test01']));
    }

    /**
     * Test it can get.
     *
     * @return void
     */
    public function testItCanGet(): void
    {
        $container = new Container();
        $container->set('test01', 1);

        $this->assertEquals(1, $container['test01']);
    }

    /**
     * Test it can set.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanSet(): void
    {
        $container = new Container();
        $container['test01'] = 1;

        $this->assertEquals(1, $container->get('test01'));
    }

    /**
     * Test it can unset.
     *
     * @return void
     */
    public function testItCanUnset(): void
    {
        $container = new Container();
        $container->set('test01', 1);
        unset($container['test01']);

        $this->assertFalse($container->has('test01'));
    }
}
