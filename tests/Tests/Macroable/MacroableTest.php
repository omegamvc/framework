<?php

/**
 * Part of Omega - Tests\Macroable Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Macroable;

use Omega\Macroable\Exceptions\MacroNotFoundException;
use Omega\Macroable\Stubs\MacroableTraitStub;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test class for the MacroableTrait functionality.
 *
 * This class tests the ability to dynamically add, check, and invoke macros
 * using the MacroableTrait in both instance and static contexts.
 * It ensures that macros can be registered, retrieved, and that exceptions
 * are properly thrown when invoking unregistered macros.
 *
 * @category  Omega\Tests
 * @package   Macroable
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(MacroableTraitStub::class)]
class MacroableTest extends TestCase
{
    /** @var object Holds mockClass. */
    protected object $mockClass;

    /**
     * Set up the test environment before each test.
     *
     * This method is called before each test method is run.
     * Override it to initialize objects, mock dependencies, or reset state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        // Use the dedicated Macroable class to test the MacroableTrait with proper coverage.
        // This class is internal and designed specifically for this purpose.
        $this->mockClass = new MacroableTraitStub();
    }

    /**
     * Clean up the test environment after each test.
     *
     * This method flushes and resets the application container
     * to ensure a clean state between tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->mockClass->resetMacro();
    }

    /**
     * Test it can add macro.
     *
     * @return void
     */
    public function testItCanAddMacro(): void
    {
        $this->mockClass->macro('test', fn (): bool => true);
        $this->mockClass->macro('test_param', fn (bool $bool): bool => $bool);

        $this->assertTrue($this->mockClass->test());
        $this->assertTrue($this->mockClass->test_param(true));
    }

    /**
     * Test it can add macro static.
     *
     * @return void
     */
    public function testItCanAddMacroStatic(): void
    {
        $this->mockClass->macro('test', fn (): bool => true);
        $this->mockClass->macro('test_param', fn (bool $bool): bool => $bool);

        $this->assertTrue($this->mockClass::test());
        $this->assertTrue($this->mockClass::test_param(true));
    }

    /**
     * Test it can check macro.
     *
     * @return void
     */
    public function testItCanCheckMacro(): void
    {
        $this->mockClass->macro('test', fn (): bool => true);

        $this->assertTrue($this->mockClass->hasMacro('test'));
        $this->assertFalse($this->mockClass->hasMacro('test2'));
    }

    /**
     * Test it throw when macro not register.
     *
     * @return void
     */
    public function testItThrowWhenMacroNotRegister(): void
    {
        $this->expectException(MacroNotFoundException::class);

        $this->mockClass->test();
    }
}
