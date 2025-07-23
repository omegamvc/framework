<?php

declare(strict_types=1);

namespace Tests\Validator\Collection;

use Omega\Validator\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
class CountingCollectionTest extends TestCase
{
    /**
     * Test it count total items in collection.
     *
     * @return void
     */
    public function testItCanCountTotalItemsInCollection(): void
    {
        $collection = Collection::make([
            'key_1' => 'item_1',
            'key_2' => 'item_2',
            'key_3' => 'item_3',
            'key_4' => 'item_4',
            'key_5' => 'item_5',
        ]);

        $this->assertCount(5, $collection);
        $this->assertEquals(5, $collection->count());
    }

    /**
     * Test it can count total items in empty collection.
     * @return void
     */
    public function testICountTotalItems_inEmptyCollection(): void
    {
        $collection = Collection::make();

        $this->assertCount(0, $collection);
        $this->assertEquals(0, $collection->count());
    }
}
