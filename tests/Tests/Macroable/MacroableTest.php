<?php

/**
 * Part of Omega - Tests\Macroable Package.
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Macroable;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Omega\Macroable\Exceptions\MacroNotFoundException;
use Omega\Macroable\MacroableTrait;

/**
 * Test suite for the MacroableTrait functionality.
 *
 * Ensures that macros can be registered, invoked, checked, and that missing macros
 * result in the appropriate exception being thrown.
 *
 * @category  Tests
 * @package   Macroable
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversTrait(MacroableTrait::class)]
final class MacroableTest extends TestCase
{
    /**
     * An anonymous mock instance using the MacroableTrait for testing.
     *
     * @var object
     */
    protected $mockClass;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->mockClass = new class {
            use MacroableTrait;
        };
    }

    /**
     * {@inheritdoc}
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
    public function itCanCheckMacro(): void
    {
        $this->mockClass->macro('test', fn (): bool => true);

        $this->assertTrue($this->mockClass->hasMacro('test'));
        $this->assertFalse($this->mockClass->hasMacro('test2'));
    }

    /**
     * Test it throw when macro is not registered.
     *
     * @return void
     */
    public function testItThrowWhenMacroIsNotRegister(): void
    {
        $this->expectException(MacroNotFoundException::class);

        $this->mockClass->test();
    }
}
