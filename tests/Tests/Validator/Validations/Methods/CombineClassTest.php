<?php

declare(strict_types=1);

namespace Tests\Validator\Validations\Methods;

use Omega\Validator\Rule\Valid;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Valid::class)]
class CombineClassTest extends TestCase
{
    /**
     * Test it can combine one valid class with another.
     *
     * @return void
     */
    public function testItCanCombineValidClassWithOtherValidClass(): void
    {
        $valid = new Valid();
        $valid->required();

        $valid2 = new Valid();
        $valid2->alpha();
        $valid2->combine($valid);

        $this->assertSame('alpha|required', $valid2->get_validation());
    }
}
