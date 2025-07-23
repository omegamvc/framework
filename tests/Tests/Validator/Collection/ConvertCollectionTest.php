<?php

declare(strict_types=1);

namespace Tests\Validator\Collection;

use Omega\Validator\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
class ConvertCollectionTest extends TestCase
{
    /**
     * Test it can convert to array.
     *
     * @return void
     */
    public function testItCanConvertToArray(): void
    {
        $array = ['key' => 'item'];
        $collection = Collection::make($array);

        $this->assertEquals($array, $collection->all());
    }
}
