<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the required_file validation.
 */
#[CoversClass(Validator::class)]
class RequiredFileTest extends TestCase
{
    /**
     * Test that required_file validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderRequiredFileValidation(): void
    {
        $this->assertSame('required_file', (string) vr()->required_file());
    }

    /**
     * Test that inverted required_file validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertRequiredFileValidation(): void
    {
        $this->assertSame('invert_required_file', (string) vr()->not()->required_file());
    }

    /**
     * Helper method to validate the input.
     *
     * @param array $input
     * @param bool  $use_not
     *
     * @return bool
     */
    private function validateInput(array $input, bool $use_not = false): bool
    {
        $val = new Validator($input);

        if (!$use_not) {
            $val->field('files.test')->required_file();
            return $val->isValid();
        }

        $val->field('files.test')->not()->required_file();
        return $val->isValid();
    }

    /**
     * Test required_file validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateRequiredFileWithCorrectInput(): void
    {
        $correct = [
            'files' => [
                'test' => [
                    'name'     => 'screenshot.png',
                    'type'     => 'image/png',
                    'tmp_name' => '/tmp/phphjatI9',
                    'error'    => 0,
                    'size'     => 22068,
                ],
            ],
        ];

        $this->assertTrue($this->validateInput($correct));
    }

    /**
     * Test required_file (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateRequiredFileNotWithCorrectInput(): void
    {
        $correct = [
            'files' => [
                'test' => [
                    'name'     => 'screenshot.png',
                    'type'     => 'image/png',
                    'tmp_name' => '/tmp/phphjatI9',
                    'error'    => 0,
                    'size'     => 22068,
                ],
            ],
        ];

        $this->assertFalse($this->validateInput($correct, true));
    }

    /**
     * Test required_file validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateRequiredFileWithIncorrectInput(): void
    {
        $incorrect = [
            'files' => [],
        ];

        $this->assertFalse($this->validateInput($incorrect));
    }

    /**
     * Test required_file (not) validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateRequiredFileNotWithIncorrectInput(): void
    {
        $incorrect = [
            'files' => [],
        ];

        $this->assertTrue($this->validateInput($incorrect, true));
    }
}
