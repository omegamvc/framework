<?php

declare(strict_types=1);

namespace Tests\Validator\Collection;

use Omega\Validator\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
class CheckingCollectionTest extends TestCase
{
    /**
     * Test it can check item with existing key.
     *
     * @return void
     */
    public function testItCanCheckItemWithExistingKey(): void
    {
        $collection = Collection::make(['key' => 'item']);
        $this->assertTrue($collection->has('key'));
    }

    public function testItCanCheckItemWithNonExistingKey(): void
    {
        $collection = Collection::make();
        $this->assertFalse($collection->has('key'));
    }
}
