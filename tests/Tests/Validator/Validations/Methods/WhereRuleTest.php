<?php

namespace Tests\Validator\Validations\Methods;

use Exception;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function Omega\Validator\vr;

/**
 * Test it can conditionally apply validation rules using the `where` method.
 */
#[CoversClass(Validator::class)]
class WhereRuleTest extends TestCase
{
    /**
     * Test it can render validation rule using method where true.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderValidationRuleUsingMethodWhereTrue(): void
    {
        $this->assertSame('required', vr()->required()->where(fn () => true));
    }

    /**
     * Test it can render validation rule using method where false.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanRenderValidationRuleUsingMethodWhereFalse(): void
    {
        $this->assertSame('', vr()->required()->where(fn () => false));
    }

    /**
     * Test it can reset validation rule using method where true.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanResetValidationRuleUsingMethodWhereTrue(): void
    {
        $val = new Validator();
        $val->field('test')->required()->where(fn () => true);

        $this->assertFalse($val->isValid());
    }

    /**
     * Test it can reset validation rule using method where false.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanResetValidationRuleUsingMethodWhereFalse(): void
    {
        $val = new Validator();
        $val->test->required()->where(fn () => false);

        $this->assertTrue($val->isValid());
    }

    /**
     * Test it can reset validation rule using method where with no return.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanResetValidationRuleUsingMethodWhereWithNoReturn(): void
    {
        $val = new Validator();

        $this->expectExceptionMessage('Condition closure not return boolean');

        $val->test->required()->where(function () {
            // intentionally empty
        });
    }

    /**
     * Test it can reset validation rule using method where with non-boolean.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanResetValidationRuleUsingMethodWhereWithNonBoolean(): void
    {
        $val = new Validator();

        $this->expectExceptionMessage('Condition closure not return boolean');

        $val->test->required()->where(fn () => 'test');
    }

    /**
     * Test it can validate combine with submitted method.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanValidateCombineWithSubmittedMethod(): void
    {
        $val = new Validator();

        $val->field('test')->required()->where(fn () => $val->submitted());

        $this->assertTrue($val->isValid());
    }
}
