<?php

namespace Tests\Validator\Unit;

use Exception;
use Omega\Validator\Rule\ValidPool;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidPool::class)]
#[CoversClass(Validator::class)]
class RunValidationTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_METHOD']);
    }

    public function testCanRunValidationUsingIsValid(): void
    {
        $valid = new Validator(['test' => 'test']);
        $valid->test->required();
        $this->assertTrue($valid->isValid());
    }

    public function testCanRunValidationUsingIsValidWithPostMethod(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $valid = new Validator(['test' => 'test']);
        $valid->test->required();
        $this->assertTrue($valid->isValid());
    }

    public function testValidationFalseBecausePassRequiredValidValidation(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $valid = Validator::make()->validation(fn (ValidPool $v) => [
            $v('test')->required(),
        ]);
        $this->assertFalse($valid->passed());
    }

    public function testValidationFalseBecausePassRequiredSubmittedForm(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $valid = Validator::make();
        $this->assertFalse($valid->passed());
    }

    public function testValidationFalseBecausePassRequiredSubmittedFormAndValidValidation(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $valid = Validator::make()->validation(fn (ValidPool $v) => [
            $v('test')->required(),
        ]);
        $this->assertFalse($valid->passed());
    }

    public function testCanRunValidationUsingIsValidWithClosureParam(): void
    {
        $valid = new Validator([
            'test1' => 'test',
            'test2' => 'test',
            'test3' => 'test',
            'test4' => 'test',
            'test5' => 'test',
            'test6' => 'test',
            'test7' => 'test',
        ]);

        $result = $valid->isValid(
            fn (ValidPool $pool) => [
                $pool->rule('test1')->required(),
                $pool('test2')->required(),
                $pool->test3->required(),
                $pool->rule('test4', 'test5')->required(),
                $pool('test6', 'test7')->required(),
            ]
        );
        $this->assertTrue($result);
    }

    public function testCanRunValidationUsingIsValidWithClosureReturn(): void
    {
        $valid = new Validator([
            'test1' => 'test',
            'test2' => 'test',
            'test3' => 'test',
            'test4' => 'test',
            'test5' => 'test',
        ]);

        $result = $valid->isValid(function () {
            $pool = new ValidPool();
            $pool->rule('test1')->required();
            $pool('test2')->required();
            $pool->test3->required();
            $pool->rule('test4', 'test5')->required();

            return $pool;
        });
        $this->assertTrue($result);
    }

    public function testCanRunValidationUsingIfValid(): void
    {
        $valid = new Validator(['test' => 'test']);
        $valid->test->required();

        $calledTrue = false;
        $calledElse = false;

        $valid->if_valid(function () use (&$calledTrue) {
            $calledTrue = true;
        })->else(function ($err) use (&$calledElse) {
            $calledElse = true;
        });

        $this->assertTrue($calledTrue);
        //$this->assertFalse($calledElse);
    }

    /**
     * @throws Exception
     */
    public function testCanRunValidationUsingValidOrException(): void
    {
        $valid = new Validator(['test' => 'test']);
        $valid->test->required();
        $this->assertTrue($valid->validOrException());
    }

    public function testCanRunValidationUsingValidOrExceptionButNotValid(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessageMatches('/validate if fallen/');

        $valid = new Validator(['test' => 'test']);
        $valid->test->required()->min_len(5);
        $valid->validOrException();
    }

    public function testCanRunValidationUsingValidOrError(): void
    {
        $valid = new Validator(['test' => 'test']);
        $valid->test->required();
        $this->assertTrue($valid->validOrError());
    }

    public function testCanRunValidationUsingValidOrErrorButNotValid(): void
    {
        $valid = new Validator(['test' => 'test']);
        $valid->test->required()->min_len(5);
        $result = $valid->validOrError();
        $this->assertIsArray($result);
    }

    public function testIsErrorIsInverseOfIsValid(): void
    {
        $valid = new Validator(['test' => 'test']);
        $valid->test->required();

        $this->assertFalse($valid->is_error());
        $this->assertNotEquals($valid->is_error(), $valid->isValid());
    }

    public function testFailsIsInverseOfPassed(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $valid = new Validator(['test' => 'test']);
        $valid->test->required();

        $this->assertFalse($valid->fails());
        $this->assertNotEquals($valid->fails(), $valid->passed());
    }
}
