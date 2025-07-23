<?php

namespace Tests\Validator\Validations\Methods;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test it can add and combine raw validation rules dynamically.
 */
#[CoversClass(Validator::class)]
class RawCombineTest extends TestCase
{
    /**
     * Test it can add validation using raw rule.
     *
     * @return void
     */
    public function testItCanAddValidationUsingRaw(): void
    {
        $validation = new Validator(['test' => 'test']);

        $validation->field('test')->raw('required');

        $this->assertTrue($validation->isValid());
    }

    /**
     * Test it can add raw rule and combine it with another rule.
     *
     * @return void
     */
    public function testItCanAddValidationUsingRawCombineWithOther(): void
    {
        $validation = new Validator(['test' => 'test']);

        $validation->field('test')->raw('required')->min_len(5);

        $this->assertFalse($validation->isValid());
    }
}
