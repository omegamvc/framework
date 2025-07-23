<?php

declare(strict_types=1);

namespace Tests\Validator\Collection;

use Omega\Validator\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
class AddingCollectionTest extends TestCase
{
    /**
     * Test it can add array using constructor.
     *
     * @return void
     */
    public function testItCanAddArrayUsingConstructor(): void
    {
        $collection = new Collection(['key' => 'value']);
        $this->assertEquals('value', $collection->key);
    }

    /**
     * Test it can add array using make.
     *
     * @return void
     */
    public function testICanAddArrayUsingMake(): void
    {
        $collection = Collection::make(['key' => 'value']);
        $this->assertEquals('value', $collection->key);
    }

    /**
     * Test it can add array using replace.
     *
     * @return void
     */
    public function testItCanAddArrayUsingReplace(): void
    {
        $collection = new Collection();
        $collection->replace(['key' => 'value']);
        $this->assertEquals('value', $collection->key);
    }

    /**
     * Test it can add array using set.
     *
     * @return void
     */
    public function testItCanAddArrayUsingSet(): void
    {
        $collection = new Collection();
        $collection->set('key', 'value');
        $this->assertEquals('value', $collection->key);
    }

    /**
     * Test it can edit existing array using set.
     *
     * @return void
     */
    public function testItCanEditExistingArrayUsingSet(): void
    {
        $collection = new Collection(['key' => 'value']);
        $collection->set('key', 'new value');
        $this->assertEquals('new value', $collection->key);
    }

    /**
     * Test it can add array using magic set.
     *
     * @return void
     */
    public function testItCanAddArrayUsingMagicSet(): void
    {
        $collection = new Collection();
        $collection->key = 'value';
        $this->assertEquals('value', $collection->key);
    }

    /**
     * est it can edit existing array using  magic set.
     *
     * @return void
     */
    public function can_edit_existing_array_using_magic_set(): void
    {
        $collection = new Collection(['key' => 'value']);
        $collection->key = 'new value';
        $this->assertEquals('new value', $collection->key);
    }
}
