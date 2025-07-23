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
use Omega\Container\ContainerInterface;
use Omega\Container\Exceptions\AliasConflictException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function method_exists;

/**
 * Abstract test suite for validating any implementation of the ContainerInterface.
 *
 * This test class defines a series of reusable tests to verify compliance with expected
 * container behaviors such as aliasing, flushing, and error handling. Concrete implementations
 * must extend this class and provide a working instance of ContainerInterface through
 * the getContainer() method.
 *
 * @internal
 * @category  Omega\Tests
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversNothing]
abstract class AbstractContainerInterface extends TestCase
{
    /**
     * Returns an instance of a class that implements ContainerInterface.
     *
     * This method must be implemented by concrete test classes in order to
     * provide the specific container implementation to be tested.
     *
     * @return ContainerInterface An instance of a container implementation.
     */
    abstract protected function getContainer(): ContainerInterface;

    /**
     * Verifies that the container can register and resolve aliases correctly.
     *
     * This test requires the container to implement `set()` and `get()` methods,
     * and will be skipped if they are not available.
     *
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testItCanAlias(): void
    {
        $container = $this->getContainer();
        $container->alias('framework', 'fast');

        // Skip logic here if `set`/`get` are not in the interface.
        if (!method_exists($container, 'set') || !method_exists($container, 'get')) {
            $this->markTestSkipped('Requires set/get methods to test aliasing.');
        }

        $container->set('framework', fn () => 'php-mvc');

        $this->assertEquals($container->get('framework'), $container->get('fast'));
    }

    /**
     * Verifies that aliasing an abstract to itself throws an AliasConflictException.
     *
     * @return void
     */
    public function testItThrowsAliasConflictExceptionWhenAliasEqualsAbstract(): void
    {
        $container = $this->getContainer();
        $container->set('framework', fn () => 'php-mvc');

        $this->expectException(AliasConflictException::class);
        $this->expectExceptionMessage(
            "Cannot register alias 'framework' for 'framework': alias and abstract must be different."
        );

        $container->alias('framework', 'framework');
    }

    /**
     * Verifies that the container flushes all internal definitions and aliases correctly.
     *
     * @return void
     */
    public function testItCanFlushContainer(): void
    {
        $container = $this->getContainer();

        $container->alias('framework', 'fast');
        $container->set('framework', fn () => 'php-mvc');

        $container->flush();

        $this->assertSame('fast', $container->getAlias('fast'));
    }
}
