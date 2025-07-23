<?php

namespace Tests\Validator\Unit;

use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validator::class)]
class OtherPropertyValidationTest extends TestCase
{
    public function testPropertyNotSameResultWithMethodNot(): void
    {
        $val = new Validator();

        $val->field('test')->not->required();
        $val->test2->not->required();

        $this->assertTrue($val->isValid());
    }
}
