<?php

/**
 * Part of Omega - Tests\Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Console;

use Omega\Console\Stubs\CommandTraitStub;
use Omega\Console\Style\Color\ForegroundColor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function chr;
use function ob_get_clean;
use function ob_start;
use function sprintf;

/**
 * Unit test for testing the CommandTraitStub methods.
 *
 * This test class verifies that the methods defined in the CommandTraitStub
 * correctly output colored text in the console. It includes tests for the
 * `echoTextRed`, `echoTextYellow`, `echoTextGreen`, and `textColor` methods.
 * These methods are expected to output text with specific foreground colors.
 *
 * @category  Omega\Tests
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(CommandTraitStub::class)]
#[CoversClass(ForegroundColor::class)]
class TraitCommandTest extends TestCase
{
    /** @var CommandTraitStub Instance of the CommandTraitStub used for testing. */
    private CommandTraitStub $command;

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
        $this->command = new class (['omega', '--test']) extends CommandTraitStub {
            public function __call($name, $arguments)
            {
                if ($name === 'echoTextRed') {
                    echo $this->textRed('Color');
                }
                if ($name === 'echoTextYellow') {
                    echo $this->textYellow('Color');
                }
                if ($name === 'echoTextGreen') {
                    echo $this->textGreen('Color');
                }
                if ($name === 'textColor') {
                    echo $this->textColor($arguments[0], 'Color');
                }
            }
        };
    }

    /**
     * Test it can make text red.
     *
     * @return void
     */
    public function testItCanMakeTextRed(): void
    {
        ob_start();
        $this->command->echoTextRed();
        $out = ob_get_clean();

        $this->assertEquals(sprintf('%s[31mColor%s[0m', chr(27), chr(27)), $out);
    }

    /**
     * Test it can make text yellow.
     *
     * @return void
     */
    public function testItCanMakeTextYellow(): void
    {
        ob_start();
        $this->command->echoTextYellow();
        $out = ob_get_clean();

        $this->assertEquals(sprintf('%s[33mColor%s[0m', chr(27), chr(27)), $out);
    }

    /**
     * Test it can make text green.
     *
     * @return void
     */
    public function testItCanMakeTextGreen(): void
    {
        ob_start();
        $this->command->echoTextGreen();
        $out = ob_get_clean();

        $this->assertEquals(sprintf('%s[32mColor%s[0m', chr(27), chr(27)), $out);
    }

    /**
     * Test it can make text color.
     *
     * @return void
     */
    public function testItCanMakeTextColor(): void
    {
        $color = new ForegroundColor([38, 2, 0, 0, 0]);
        ob_start();
        $this->command->textColor($color, 'Color');
        $out = ob_get_clean();

        $this->assertEquals(sprintf('%s[38;2;0;0;0mColor%s[0m', chr(27), chr(27)), $out);
    }
}
