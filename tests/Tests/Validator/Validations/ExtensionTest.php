<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the file extension validation rule.
 */
#[CoversClass(Validator::class)]
class ExtensionTest extends TestCase
{
    /**
     * Test that extension validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderExtensionValidation(): void
    {
        $rule = vr()->extension('png', 'jpg', 'gif');
        $this->assertSame('extension,png;jpg;gif', (string)$rule);
    }

    /**
     * Test that inverted extension validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertExtensionValidation(): void
    {
        $rule = vr()->not->extension('png', 'jpg', 'gif');
        $this->assertSame('invert_extension,png;jpg;gif', (string)$rule);
    }

    /**
     * Test extension validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateExtensionWithCorrectInput(): void
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

        $val = new Validator($correct);
        $val->field('files.test')->extension('png');

        $this->assertTrue($val->isValid());
    }

    /**
     * Test inverted extension validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateInvertExtensionWithCorrectInput(): void
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

        $val = new Validator($correct);
        $val->field('files.test')->not->extension('png');

        $this->assertFalse($val->isValid());
    }

    /**
     * Test extension validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateExtensionWithIncorrectInput(): void
    {
        $incorrect = [
            'files' => [
                'test' => [
                    'name'     => 'screenshot.png',
                    'type'     => 'image/png',
                    'tmp_name' => '/tmp/phphjatI9',
                    'error'    => 4,
                    'size'     => 22068,
                ],
            ],
        ];

        $val = new Validator($incorrect);
        $val->field('files.test')->extension('php');

        $this->assertFalse($val->isValid());
    }

    /**
     * Test inverted extension validation with incorrect input.
     *
     * @return void
     */
    public function testItCanValidateInvertExtensionWithIncorrectInput(): void
    {
        $incorrect = [
            'files' => [
                'test' => [
                    'name'     => 'screenshot.png',
                    'type'     => 'image/png',
                    'tmp_name' => '/tmp/phphjatI9',
                    'error'    => 4,
                    'size'     => 22068,
                ],
            ],
        ];

        $val = new Validator($incorrect);
        $val->field('files.test')->not->extension('php');

        $this->assertTrue($val->isValid());
    }
}
