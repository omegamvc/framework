<?php

namespace Tests\Validator\Unit;

use Omega\Validator\Rule\ValidPool;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test adding validation rules in various ways.
 */
#[CoversClass(ValidPool::class)]
#[CoversClass(Validator::class)]
class AddValidRuleValidationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test adding validation using method field.
     *
     * @return void
     */
    public function testItCanAddValidationUsingMethodField(): void
    {
        $valid = new Validator(['test' => 'test']);

        $valid->field('test')->required();

        $this->assertTrue($valid->isValid());
    }

    /**
     * Test adding validation using magic __get.
     *
     * @return void
     */
    public function testItCanAddValidationUsingMagicGet(): void
    {
        $valid = new Validator(['test' => 'test']);

        $valid->test->required();

        $this->assertTrue($valid->isValid());
    }

    /**
     * Test adding validation using magic __invoke.
     *
     * @return void
     */
    public function testItCanAddValidationUsingMagicInvoke(): void
    {
        $valid = new Validator(['test' => 'test']);

        $valid('test')->required();

        $this->assertTrue($valid->isValid());
    }

    /**
     * Test adding validation using magic __set.
     *
     * @return void
     */
    public function testItCanAddValidationUsingMagicSet(): void
    {
        $valid = new Validator(['test' => 'test']);

        $valid->test = 'required';

        $this->assertTrue($valid->isValid());
    }

    /**
     * Test adding validator rule using method validation with param callback.
     *
     * @return void
     */
    public function testItCanAddValidatorRuleUsingValidationMethodParam(): void
    {
        $v = Validator::make(['test' => 'test'])
            ->validation(function () {
                $v = new ValidPool();
                $v('test')->required();

                return $v;
            })
            ->isValid();

        $this->assertTrue($v);
    }

    /**
     * Test adding validator rule using method validation with return callback.
     *
     * @return void
     */
    public function testItCanAddValidatorRuleUsingValidationMethodReturn(): void
    {
        $v = Validator::make(['test' => 'test'])
            ->validation(fn (ValidPool $v) => [
                $v('test')->required(),
            ])
            ->isValid();

        $this->assertTrue($v);
    }

    /**
     * Test adding validator rule using pools callback from make() with param callback.
     *
     * @return void
     */
    public function testItCanAddValidatorRuleUsingPoolsCallbackFromMakeParam(): void
    {
        $v = Validator::make(
            ['test' => 123],
            fn (ValidPool $v) => [
                $v('test')->required(),
                $v('d')->alpha(),
            ]
        )->isValid();

        $this->assertTrue($v);
    }

    /**
     * Test adding validator rule using pools callback from make() with return callback.
     *
     * @return void
     */
    public function testItCanAddValidatorRuleUsingPoolsCallbackFromMakeReturn(): void
    {
        $v = Validator::make(
            ['test' => 123],
            function () {
                $v = new ValidPool();
                $v('test')->required();
                $v('d')->alpha();

                return $v;
            }
        )->isValid();

        $this->assertTrue($v);
    }

    /**
     * Test adding new valid rule with existing field.
     *
     * @return void
     */
    public function testItCanAddNewValidRuleWithExistField(): void
    {
        $validation = new Validator(['test' => 'test']);

        $validation->field('test')->max_len(4);
        $validation->field('test')->required();

        $this->assertTrue($validation->isValid());
    }

    /**
     * Test adding new valid rule with existing field using validpool.
     *
     * @return void
     */
    public function testItCanAddNewValidRuleWithExistFieldUsingValidPool(): void
    {
        $validation = new Validator(['test' => 'test']);
        $validation->validation(fn ($valid) => [
            $valid('test')->max_len(4),
            $valid('test')->required(),
        ]);

        $this->assertTrue($validation->isValid());
    }

    /**
     * Test adding multiple fields using method field.
     *
     * @return void
     */
    public function testItCanAddMultyFieldUsingMethodField(): void
    {
        $valid = new Validator(['test' => 'test', 'test2' => 'test']);

        $valid->field('test', 'test2')->required();

        $this->assertTrue($valid->isValid());
    }

    /**
     * Test adding multiple fields using method field with existing fields.
     *
     * @return void
     */
    public function testItCanAddMultyFieldUsingMethodFieldWithFieldExist(): void
    {
        $valid = new Validator(['test' => 'test', 'test2' => 'test2']);

        $valid->field('test', 'test2')->required();
        $valid->field('test')->max_len(4);
        $valid->field('test2')->max_len(5);
        $valid->field('test', 'test2')->min_len(4);

        $this->assertTrue($valid->isValid());
    }

    /**
     * Test adding multiple fields using method __invoke.
     *
     * @return void
     */
    public function testItCanAddMultyFieldUsingMethodInvoke(): void
    {
        $valid = new Validator(['test' => 'test', 'test2' => 'test']);

        $valid('test', 'test2')->required();

        $this->assertTrue($valid->isValid());
    }

    /**
     * Test adding multiple fields using method __invoke with existing fields.
     *
     * @return void
     */
    public function testItCanAddMultyFieldUsingMethodInvokeWithFieldExist(): void
    {
        $valid = new Validator(['test' => 'test', 'test2' => 'test2']);

        $valid('test', 'test2')->required();
        $valid->field('test')->max_len(4);
        $valid->field('test2')->max_len(5);
        $valid('test', 'test2')->min_len(4);

        $this->assertTrue($valid->isValid());
    }
}
