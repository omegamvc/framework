<?php

declare(strict_types=1);

namespace Tests\Validator;

use Omega\Validator\Rule\ValidPool;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidPool::class)]
#[CoversClass(Validator::class)]
class ValidatorTest extends TestCase
{
    /**
     * Test validate using fields and validation.
     *
     * @return void
     */
    public function testValidateUsingFieldsAndValidation(): void
    {
        $input = [
            'id'       => 1,
            'user'     => 'teguh',
            'name'     => 'teguh agus',
            'favorite' => ['manggo', 'durian', 'start fruite'],
        ];
        $val = new Validator($input);

        $val->id->required()->integer();
        $val->field('user')->required()->min_len(5);
        $val('name')->required()->valid_name();

        $this->assertTrue($val->isValid());
    }

    /**
     * Test run valid condition with valid condition.
     *
     * @return void
     */
    public function testRunValidationConditionWithValidCondition(): void
    {
        $input = [
            'id'       => 1,
            'user'     => 'teguh',
            'name'     => 'teguh agus',
            'favorite' => ['manggo', 'durian', 'start fruite'],
        ];
        $val = new Validator($input);

        $val->id->required()->integer();
        $val->field('user')->required()->min_len(5);
        $val('name')->required()->valid_name();

        $val->if_valid(function () use ($val) {
            $this->assertTrue($val->isValid());
        })->else(function ($err) {
            $this->assertCount(0, $err);
        });
    }

    /**
     * Test turn valid condition with failed condition.
     *
     * @return void
     */
    public function testTurnValidationConditionWithFailedCondition(): void
    {
        $input = [
            'id'       => null,
            'user'     => 'tgh',
            'name'     => 'teguh agus',
            'favorite' => ['manggo', 'durian', 'start fruite'],
        ];
        $val = new Validator($input);

        $val->id->required()->integer();
        $val->field('user')->required()->min_len(5);
        $val('name')->required()->valid_name();

        $val->if_valid(function () {
            $this->fail('if_valid callback executed despite failed validation.');
        })->else(function ($err) use ($val) {
            $this->assertFalse($val->isValid());
        });
    }

    /**
     * Test it can validate nesting array.
     *
     * @return void
     */
    public function testItCanValidateNestingArray(): void
    {
        $test = new Validator([
            'name'  => 'angger',
            'nest'  => [
                'number' => 12,
                'string' => 'a string',
            ],
            'users' => [
                ['name' => 'ulfa', 'age' => 21],
                ['name' => 'haikal', 'age' => 1],
            ],
        ]);

        $valid = $test->isValid(function (ValidPool $valid) {
            $valid('name')->required()->max_len(7);
            $valid('nest.number')->required();
            $valid('users.*.name')->required();
        });

        $this->assertTrue($valid);
    }

    /**
     * Test it can validate invert nesting array.
     *
     * @return void
     */
    public function testItCanValidateInvertNestingArray(): void
    {
        $test = new Validator([
            // 'name' is intentionally missing
            'hoby'  => 'plaing,game',
            'nest'  => [
                'number' => 12,
                'string' => 'a string',
            ],
            'users' => [
                ['name' => 'ulfa', 'age' => 21],
                ['name' => 'haikal', 'age' => 1],
            ],
        ]);

        $valid = $test->isValid(function (ValidPool $valid) {
            $valid('name')->not()->required();
            $valid('hoby')->not()->contains();
            $valid('nest.number')->required();
            $valid('users.*.name')->required();
        });

        $this->assertTrue($valid);
    }
}
