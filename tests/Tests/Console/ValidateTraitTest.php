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

use Omega\Console\Stubs\ValidateCommandTraitStub;
use Omega\Console\Style\Style;
use Omega\Text\Str;
use Omega\Validator\Rule\ValidPool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function ob_get_clean;
use function ob_start;

/**
 * Unit test for the ValidateCommandTrait functionality.
 *
 * This test class verifies that the ValidateCommandTrait correctly applies
 * validation rules and produces appropriate error messages when validation fails.
 * It uses an anonymous subclass of ValidateCommandTraitStub to simulate a real
 * command execution with predefined validation logic.
 *
 * The test focuses on the integration between command options, the validation
 * rule system, and the resulting output message.
 *
 * @category  Omega\Tests
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Style::class)]
#[CoversClass(ValidPool::class)]
#[CoversClass(ValidateCommandTraitStub::class)]
class ValidateTraitTest extends TestCase
{
    /** @var ValidateCommandTraitStub Instance of the ValidateCommandTraitStub used for testing. */
    private ValidateCommandTraitStub $command;

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
        $this->command = new class (['php', 'omega', '--test', 'oke']) extends ValidateCommandTraitStub {
            public function main(): void
            {
                $this->initValidate($this->optionMapper);
                $this->getValidateMessage(new Style())->out(false);
            }

            protected function validateRule(ValidPool $rules): void
            {
                $rules('test')->required()->min_len(5);
            }
        };
    }

    /**
     * Test it can make text red
     *
     * @return void
     */
    public function testItCanMakeTextRed(): void
    {
        ob_start();
        $this->command->main();
        $out = ob_get_clean();

        $this->assertTrue(Str::contains($out, 'The Test field needs to be at least 5 characters'));
    }
}
