<?php

declare(strict_types=1);

namespace Tests\Validator\Unit;

use Omega\Validator\Rule\ValidPool;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for filtering valid rules with allowed and excepted fields.
 */
#[CoversClass(ValidPool::class)]
#[CoversClass(Validator::class)]
class FilterValidRuleValidationTest extends TestCase
{
    /**
     * Test normal validation behavior.
     *
     * @return void
     */
    public function testItCanValidateWithNormalBehavior(): void
    {
        $v = Validator::make(
            [
                'test1' => 'test',
                'test2' => 'test',
                'test3' => '',
            ],
            fn (ValidPool $v) => [
                $v('test1')->required(),
                $v('test2')->required(),
                $v('test3')->required(),
            ]
        );

        $this->assertFalse($v->isValid());
    }

    /**
     * Test filtering valid rule with allowed fields.
     *
     * @return void
     */
    public function testItCanFilterValidRuleWithAllowedField(): void
    {
        $v = Validator::make(
            [
                'test1' => 'test',
                'test2' => 'test',
                'test3' => '',
            ],
            fn (ValidPool $v) => [
                $v('test1')->required(),
                $v('test2')->required(),
                $v('test3')->required(),
            ]
        );

        $v->only(['test1', 'test2']);

        $this->assertTrue($v->isValid());
    }

    /**
     * Test filtering valid rule with allowed field that fails validation.
     *
     * @return void
     */
    public function testItCanFilterValidRuleWithAllowedFieldFalse(): void
    {
        $v = Validator::make(
            [
                'test1' => 'test',
                'test2' => 'test',
                'test3' => '',
            ],
            fn (ValidPool $v) => [
                $v('test1')->required(),
                $v('test2')->required(),
                $v('test3')->required(),
            ]
        );

        $v->only(['test3']);

        $this->assertFalse($v->isValid());
    }

    /**
     * Test filtering valid rule with allowed field not existing.
     *
     * @return void
     */
    public function testItCanFilterValidRuleWithAllowedFieldButNotExist(): void
    {
        $v = Validator::make(
            [
                'test1' => 'test',
                'test2' => 'test',
                'test3' => '',
            ],
            fn (ValidPool $v) => [
                $v('test1')->required(),
                $v('test2')->required(),
                $v('test3')->required(),
            ]
        );

        $v->only(['test4']);

        $this->assertTrue($v->isValid());
    }

    /**
     * Test filtering valid rule with excepted fields.
     *
     * @return void
     */
    public function testItCanFilterValidRuleWithExceptedField(): void
    {
        $v = Validator::make(
            [
                'test1' => 'test',
                'test2' => 'test',
                'test3' => '',
            ],
            fn (ValidPool $v) => [
                $v('test1')->required(),
                $v('test2')->required(),
                $v('test3')->required(),
            ]
        );

        $v->except(['test1', 'test2']);

        $this->assertFalse($v->isValid());
    }

    /**
     * Test filtering valid rule with excepted field not existing.
     *
     * @return void
     */
    public function testItCanFilterValidRuleWithExceptedFieldButNotExist(): void
    {
        $v = Validator::make(
            [
                'test1' => 'test',
                'test2' => 'test',
                'test3' => '',
            ],
            fn (ValidPool $v) => [
                $v('test1')->required(),
                $v('test2')->required(),
                $v('test3')->required(),
            ]
        );

        $v->except(['test4']);

        $this->assertFalse($v->isValid());
    }
}
