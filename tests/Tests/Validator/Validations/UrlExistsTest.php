<?php

namespace Tests\Validator\Validations;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Tests for the url_exists validation.
 */
#[CoversClass(Validator::class)]
class UrlExistsTest extends TestCase
{
    /**
     * Test that url_exists validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderUrlExistsValidation(): void
    {
        $this->assertSame('url_exists', (string) vr()->url_exists());
    }

    /**
     * Test that inverted url_exists validation renders correctly.
     *
     * @return void
     */
    public function testItCanRenderInvertUrlExistsValidation(): void
    {
        $this->assertSame('invert_url_exists', (string) vr()->not()->url_exists());
    }

    /**
     * Test url_exists validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateUrlExistsWithCorrectInput(): void
    {
        $correct = ['test' => 'https://google.com/'];

        $val = new Validator($correct);
        $val->test->url_exists();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test url_exists (not) validation with correct input.
     *
     * @return void
     */
    public function testItCanValidateNotUrlExistsWithCorrectInput(): void
    {
        $correct = ['test' => 'https://google.com/'];

        $val = new Validator($correct);
        $val->test->not()->url_exists();

        $this->assertFalse($val->isValid());
    }
}
