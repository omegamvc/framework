<?php

namespace Tests\Validator\Unit;

use ArrayIterator;
use IteratorAggregate;
use Omega\Validator\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use Traversable;

/**
 * Test adding fields to the Validator using different methods.
 */
#[CoversClass(Validator::class)]
class AddFieldValidationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test adding fields using constructor.
     *
     * @return void
     */
    public function testItCanAddFieldsUsingConstructor(): void
    {
        $fields = [
            'field_1' => 'field_1',
            'field_2' => 'field_3',
            'field_3' => 'field_3',
        ];

        $valid = new Validator($fields);
        $this->assertEquals($fields, $valid->get_fields());
    }

    /**
     * Test adding fields using static method make.
     *
     * @return void
     */
    public function testItCanAddFieldsUsingStaticMakeMethod(): void
    {
        $fields = [
            'field_1' => 'field_1',
            'field_2' => 'field_3',
            'field_3' => 'field_3',
        ];

        $valid = Validator::make($fields);
        $this->assertEquals($fields, $valid->get_fields());
    }

    /**
     * Test adding fields using method fields.
     *
     * @return void
     */
    public function testItCanAddFieldsUsingMethodFields(): void
    {
        $fields = [
            'field_1' => 'field_1',
            'field_2' => 'field_3',
            'field_3' => 'field_3',
        ];

        $valid = new Validator();
        $valid->fields($fields);
        $this->assertEquals($fields, $valid->get_fields());
    }

    /**
     * Test adding fields using iterator array.
     *
     * @return void
     */
    public function testItCanAddFieldsUsingIteratorArray(): void
    {
        $fields = new class () implements IteratorAggregate {
            private $fields = [
                'field_1' => 'test',
                'field_2' => 'test',
            ];

            public function getIterator(): Traversable
            {
                return new ArrayIterator($this->fields);
            }
        };

        $valid = new Validator();
        $valid->fields($fields);

        $this->assertEquals([
            'field_1' => 'test',
            'field_2' => 'test',
        ], $valid->get_fields());
    }
}
