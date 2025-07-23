<?php

declare(strict_types=1);

namespace Tests\Validator\Collection;

use Omega\Validator\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
class GettingCollectionTest extends TestCase
{
    /**
     * Test it can get item using get.
     *
     * @return void
     */
    public function testItCanGetItemUsingGet(): void
    {
        $collection = new Collection(['key' => 'item']);
        $this->assertEquals('item', $collection->get('key'));
    }

    /**
     * Test it can get items with default when not exists.
     *
     * @return void
     */
    public function testItCanGetItemWithDefaultWhenNotExist(): void
    {
        $collection = new Collection();
        $this->assertEquals('no item', $collection->get('key', 'no item'));
    }

    /**
     * Test it can get item without default when not exists.
     *
     * @return void
     */
    public function testItCanGetItemWithoutDefaultWhenNotExist(): void
    {
        $collection = new Collection();
        $this->assertNull($collection->get('key'));
    }

    /**
     * Test it can get item using magic get.
     *
     * @return void
     */
    public function testItCanGetItemUsingMagicGet(): void
    {
        $collection = new Collection(['key' => 'item']);
        $this->assertEquals('item', $collection->key);
    }

    /**
     * Test it can get item using magic get when not exists.
     *
     * @return void
     */
    public function testItCanGetItemUsingMagicGetWhenNotExist(): void
    {
        $collection = new Collection();
        $this->assertNull($collection->item);
    }
}
