<?php

namespace Tests\Validator\Unit;

use Exception;
use Omega\Validator\Messages\MessagePool;
use Omega\Validator\Rule;
use Omega\Validator\Rule\ValidPool;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Test customization of error messages and language support.
 */
#[CoversClass(MessagePool::class)]
#[CoversClass(Rule::class)]
#[CoversClass(ValidPool::class)]
#[CoversClass(Validator::class)]
class CustomErrorValidationTest extends TestCase
{
    /**
     * Test creating custom error message for required rule.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanCreateCustomError(): void
    {
        Rule::set_error_message('required', '{field} can\'t be null');

        $val = new Validator(['test' => null]);
        $val->test->required();

        $errors = $val->getError();
        $this->assertSame("Test can't be null", $errors['test']);
    }

    /**
     * Test creating custom error message with negated rule.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanCreateCustomErrorWithNotMethod(): void
    {
        Rule::set_error_message('required', '{field} can\'t be null');

        $val = new Validator(['test' => 'null']);
        $val->test->not()->required();

        $errors = $val->getError();
        $this->assertSame("Test can't be null", $errors['test']);
    }

    /**
     * Test creating multiple custom error messages.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanCreateMultipleCustomErrors(): void
    {
        Rule::set_error_messages([
            'required' => '{field} can\'t be null',
            'min_len'  => '{field} less that 2',
        ]);

        $val = new Validator(['test' => null, 'test2' => 'abc']);
        $val->test->required();
        $val->field('test2')->min_len(4);

        $expected = [
            'test'  => "Test can't be null",
            'test2' => 'Test2 less that 2',
        ];

        $this->assertSame($expected, $val->getError());
    }

    /**
     * Test customizing field error message via messages property.
     *
     * @return void
     */
    public function testItCanCustomizeFieldErrorMessage(): void
    {
        $v = Validator::make()->validation(fn (ValidPool $v) => [
            $v('test')->required(),
        ]);
        $v->messages()->test->required = 'custom required message';

        $this->assertSame('custom required message', $v->errors->test);
    }

    /**
     * Test customizing field error message overriding global message.
     *
     * @return void
     */
    public function testItCanCustomizeFieldErrorMessageOverrideGlobal(): void
    {
        Rule::set_error_message('required', '{field} can\'t be null');

        $v = Validator::make()->validation(fn (ValidPool $v) => [
            $v('test')->required(),
        ]);

        $v->messages()->test->required = 'custom required message';

        $this->assertSame('custom required message', $v->errors->test);
    }

    /**
     * Test customizing field error message by assigning message array.
     *
     * @return void
     */
    public function testItCanCustomizeFieldErrorMessageUsingArray(): void
    {
        $v = Validator::make()->validation(fn (ValidPool $v) => [
            $v('test')->required(),
        ]);

        $v->messages()->field('test')->required = 'custom required message';

        $this->assertSame('custom required message', $v->errors->test);
    }

    /**
     * Test customizing field error message using setErrorMessages method.
     *
     * @return void
     */
    public function testItCanCustomizeFieldErrorMessageUsingSetErrorMessages(): void
    {
        $v = Validator::make()->validation(fn (ValidPool $v) => [
            $v('test')->required(),
        ]);
        $v->setErrorMessages([
            'test' => [
                'required' => 'custom required message',
            ],
        ]);

        $this->assertSame('custom required message', $v->errors->test);
    }

    /**
     * Test customizing error message using message pool with dynamic field property.
     *
     * @return void
     */
    public function testItCanCustomizeErrorMessageUsingMessagePoolDynamicField(): void
    {
        $v = Validator::make()->validation(fn (ValidPool $v) => [
            $v('test')->required(),
        ]);
        $v->messages()->field('test')->required = 'custom required message';

        $this->assertSame('custom required message', $v->errors->test);
    }

    /**
     * Test customizing error message using messages with callback.
     *
     * @return void
     */
    public function testItCanCustomizeErrorMessageUsingMessagesWithCallback(): void
    {
        $v = Validator::make()->validation(fn (ValidPool $v) => [
            $v('test')->required(),
            $v('test2')->required(),
        ]);
        $v->messages(static function (MessagePool $message) {
            $message->field('test')->required = 'custom required message';
        })->field('test2')->required = 'custom required message';

        $this->assertSame('custom required message', $v->errors->test);
        $this->assertSame('custom required message', $v->errors->test2);
    }
}
