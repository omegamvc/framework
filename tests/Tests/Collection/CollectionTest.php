<?php

/**
 * Part of Omega - Tests\Collection Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Collection;

use Omega\Collection\Collection;
use Omega\Collection\CollectionImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_filter;
use function array_keys;
use function array_map;
use function array_reverse;
use function array_values;
use function in_array;
use function json_encode;
use function str_contains;

/**
 * Class CollectionTest
 *
 * This test suite verifies the core functionality and behavior of the Collection class,
 * ensuring correct handling of data access, mutation, filtering, mapping, sorting, and other utility methods.
 *
 * It covers:
 * - Basic item access and manipulation (get, set, remove, etc.)
 * - Advanced operations like map, filter, reject, flatten, and chunk/split
 * - Conditional checks (has, contain, some, every)
 * - Transformation methods (clone, reverse, sort, sortBy, sortKey, etc.)
 * - Conversion methods (to array, to JSON)
 * - Chainable method calls and fluent behavior
 *
 * The tests are comprehensive and ensure the Collection class behaves predictably and reliably
 * under a variety of scenarios.
 *
 * @category  Omega\Tests
 * @package   Collection
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
#[CoversClass(Collection::class)]
class CollectionTest extends TestCase
{
    /**
     * Test it collection functional work properly.
     *
     * @return void
     * @noinspection PhpConditionAlreadyCheckedInspection
     * @noinspection DuplicatedCode
     */
    public function testItCollectionFunctionalWorkProperly(): void
    {
        $original = [
            'bau_1' => 'manga',
            'bau_2' => 'jeruk',
            'bau_3' => 'ap',
            'bau_4' => 'melon',
            'bau_5' => 'rambutan',
            'bau_6' => 'peer',
        ];
        $test = new Collection($original);

        // getter
        $this->assertEquals('manga', $test->bau_1, 'add new item collection using __set');
        $this->assertEquals('manga', $test->get('bau_1'), 'add new item collection using set()');

        // add new item
        $test->set('bau_7', 'kelengkeng');
        $test->bau_8 = 'cherry';
        $this->assertEquals('cherry', $test->bau_8, 'get item collection using __get');
        $this->assertEquals('kelengkeng', $test->get('bau_7'), 'get item collection using get()');

        // rename item
        $test->set('bau_7', 'durian');
        $test->bau_8 = 'bananas';
        $this->assertEquals('bananas', $test->bau_8, 'replace exists item collection using __get');
        $this->assertEquals('durian', $test->get('bau_7'), 'replace exists item collection using get()');

        // cek array key
        $this->assertTrue($test->has('bau_1'), 'collection have item with key');

        // cek contain
        $this->assertTrue($test->contain('manga'), 'collection have item');

        // remove item
        $test->remove('bau_2');
        $this->assertFalse($test->has('bau_2'), 'remove some item using key');

        // reset to origin
        $test->replace($original);

        // count
        $this->assertEquals(6, $test->count(), 'count item in collection');

        // count by
        $countIf = $test->countIf(function ($item) {
            // find letter contain 'e' letter
            return str_contains($item, 'e');
        });
        $this->assertEquals(3, $countIf, 'count item in collection with some condition');

        // first and last item cek
        $this->assertEquals('manga', $test->first('bukan bau'), 'get first item in collection');
        $this->assertEquals('peer', $test->last('bukan bau'), 'get last item in collection');

        // test clear and empty cek
        $this->assertFalse($test->isEmpty());
        $test->clear();
        $this->assertTrue($test->isEmpty(), 'cek collection empty');
        // same with origin
        $test->replace($original);
        $this->assertEquals($test->all(), $original, 'replace axis collection with new data');

        // test array keys and values
        $keys  = array_keys($original);
        $items = array_values($original);
        $this->assertEquals($keys, $test->keys(), 'get all key in collection');
        $this->assertEquals($items, $test->items(), 'get all item value in collection');

        // each function
        $test->each(function ($item, $key) use ($original) {
            $this->assertTrue(in_array($item, $original), 'test each with value');
            $this->assertArrayHasKey($key, $original, 'test each with key');
        });

        // map function
        $test->map(fn ($item) => ucfirst($item));
        $copyOrigin = array_map(fn ($item) => ucfirst($item), $original);
        $this->assertEquals($test->all(), $copyOrigin, 'replace some/all item using map');
        $test->replace($original);

        // filter function
        $test->filter(function ($item) {
            // find letter contain 'e' letter
            return str_contains($item, 'e');
        });
        $copyOrigin = array_filter($original, function ($item) {
            // find letter contain 'e' letter
            return str_contains($item, 'e');
        });
        $this->assertEquals($test->all(), $copyOrigin, 'filter item in collection');
        $test->replace($original);

        // test the collection have item with e letter
        $some = $test->some(function ($item) {
            // find letter contain 'e' letter
            return str_contains($item, 'e');
        });
        $this->assertTrue($some, 'test the collection have item with "e" letter');

        // test the collection every item dont have 'x' letter
        $every = $test->every(function ($item) {
            // find letter contain 'x' letter
            return !str_contains($item, 'x');
        });
        $this->assertTrue($every, 'collection every item dont have "x" letter');

        // json output
        $json = json_encode($original);
        $this->assertJsonStringEqualsJsonString($test->json(), $json, 'collection convert to json string');

        // collection reverse
        $copyOrigin = $original;
        $this->assertEquals(
            $test->reverse()->all(),
            array_reverse($copyOrigin),
            'test reverse collection'
        );
        $test->replace($original);

        // sort collection
        // sort asc
        $this->assertEquals(
            'ap',
            $test->sort()->first(),
            'testing sort asc collection'
        );
        // sort desc
        $this->assertEquals(
            'rambutan',
            $test->sortDesc()->first(),
            'testing sort desc collection'
        );
        // sort using callback
        $test->sortBy(function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
        $this->assertEquals(
            'ap',
            $test->first(),
            'sort using user define ascending'
        );
        $test->sortByDesc(function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? -1 : 1;
        });
        $this->assertEquals(
            'rambutan',
            $test->first(),
            'sort using user define descending'
        );

        // sort collection by key
        $this->assertEquals(
            'manga',
            $test->sortKey()->first(),
            'sort collection asc with key'
        );
        $this->assertEquals(
            'peer',
            $test->sortKeyDesc()->first(),
            'sort collection desc with key'
        );
        $test->replace($original);

        // clone collection
        $this->assertEquals(
            $test->clone()->reverse()->first(),
            $test->last(),
            'clone collection without interrupt original'
        );

        // reject
        $copyOrigin = $original;
        unset($copyOrigin['bau_2']);
        $this->assertEquals(
            $test->reject(fn ($item) => $item == 'jeruk')->all(),
            $copyOrigin,
            'its like filter but the opposite'
        );

        // chunk
        $chunk = $test->clone()->chunk(3)->all();
        $this->assertEquals(
            [
                ['bau_1' => 'manga', 'bau_3' => 'ap', 'bau_4' => 'melon'],
                ['bau_5' => 'rambutan', 'bau_6' => 'peer'],
            ],
            $chunk,
            'chunk to 3'
        );

        // split
        $split = $test->clone()->split(3)->all();
        $this->assertEquals(
            [
                ['bau_1' => 'manga', 'bau_3' => 'ap'],
                ['bau_4' => 'melon', 'bau_5' => 'rambutan'],
                ['bau_6' => 'peer'],
            ],
            $split,
            'split to 2'
        );

        $only = $test->clone()->only(['bau_1', 'bau_5']);
        $this->assertEquals(
            ['bau_1' => 'manga', 'bau_5' => 'rambutan'],
            $only->all(),
            'show only some'
        );

        $except = $test->clone()->except(['bau_3', 'bau_4', 'bau_6']);
        $this->assertEquals(
            ['bau_1' => 'manga', 'bau_5' => 'rambutan'],
            $except->all(),
            'show list with except'
        );

        // flatten
        $array_nesting = [
            'first' => ['bau_1' => 'manga', ['bau_2' => 'jeruk', 'bau_3' => 'ap', 'bau_4' => 'melon']],
            'mid'   => ['bau_4' => 'melon', ['bau_5' => 'rambutan']],
            'last'  => ['bau_6' => 'peer'],
        ];
        $flatten = new Collection($array_nesting);
        $this->assertEquals(
            $original,
            $flatten->flatten()->all(),
            'flatten nesting array'
        );
    }

    /**
     * Test it collection chain work great.
     *
     * @return void
     */
    public function testItCollectionChainWorkGreat(): void
    {
        $origin     = [0, 1, 2, 3, 4];
        $collection = new Collection($origin);

        $chain = $collection
            ->add($origin)
            ->remove(0)
            ->set(0, 0)
            ->clear()
            ->replace($origin)
            ->each(fn ($el) => in_array($el, $origin))
            ->map(fn ($el) => $el + 100 - (2 * 50)) // equal +0
            ->filter(fn ($el) => $el > -1)
            ->sort()
            ->sortDesc()
            ->sortKey()
            ->sortKeyDesc()
            ->sortBy(function ($a, $b) {
                if ($a == $b) {
                    return 0;
                }

                return ($a < $b) ? -1 : 1;
            })
            ->sortByDesc(function ($a, $b) {
                if ($b == $a) {
                    return 0;
                }

                return ($b < $a) ? -1 : 1;
            })
            ->all()
        ;

        $this->assertEquals($chain, $origin, 'all collection with chain is work');
    }

    /**
     * Test it can add collection from collection.
     *
     * @return void
     */
    public function testItCanAddCollectionFromCollection(): void
    {
        $arr_1 = ['a' => 'b'];
        $arr_2 = ['c' => 'd'];

        $collect_1 = new Collection($arr_1);
        $collect_2 = new CollectionImmutable($arr_2);

        $collect = new Collection([]);
        $collect->ref($collect_1)->ref($collect_2);

        $this->assertEquals(['a' => 'b', 'c' => 'd'], $collect->all());
    }

    /**
     * Test it can acting like array.
     *
     * @return void
     */
    public function testItCanActingLikeArray(): void
    {
        $coll = new Collection(['one' => 1, 'two' => 2, 'three' => 3]);

        $this->assertArrayHasKey('one', $coll);
        $this->assertArrayHasKey('two', $coll);
        $this->assertArrayHasKey('three', $coll);
    }

    /**
     * Test it can do like array.
     *
     * @return void
     * @noinspection PhpConditionAlreadyCheckedInspection
     */
    public function testItCanDoLikeArray(): void
    {
        $arr  = ['one' => 1, 'two' => 2, 'three' => 3];
        $coll = new Collection($arr);

        // get
        foreach ($arr as $key => $value) {
            $this->assertEquals($value, $coll[$key]);
        }

        // set
        $coll['four'] = 4;
        $this->assertArrayHasKey('four', $coll);

        // has
        $this->assertTrue(isset($coll['four']));

        // unset
        unset($coll['four']);
        $this->assertEquals($arr, $coll->all());
    }

    /**
     * Test it can by iterator.
     *
     * @return void
     */
    public function testItCanByIterator(): void
    {
        $coll = new Collection(['one' => 1, 'two' => 2, 'three' => 3]);

        foreach ($coll as $key => $value) {
            $this->assertEquals($value, $coll[$key]);
        }
    }

    /**
     * Test it can by shuffle.
     *
     * @return void
     */
    public function testItCanByShuffle(): void
    {
        $arr  = ['one' => 1, 'two' => 2, 'three' => 3];
        $coll = new Collection($arr);

        $coll->shuffle();

        foreach ($arr as $key => $val) {
            $this->assertArrayHasKey($key, $coll);
        }
    }

    /**
     * Test it can map with keys.
     *
     * @return void
     */
    public function testItCanMapWithKeys(): void
    {
        $arr = new Collection([
            [
                'name'  => 'taylor',
                'email' => 'taylor@laravel.com',
            ], [
                'name'  => 'giovannini',
                'email' => 'giovannini@omegamvc.com',
            ],
        ]);

        $assocBy = $arr->assocBy(fn ($item) => [$item['name'] => $item['email']]);

        $this->assertEquals([
            'taylor'  => 'taylor@laravel.com',
            'giovannini' => 'giovannini@omegamvc.com',
        ], $assocBy->toArray());
    }

    /**
     * Test it can clone collection.
     *
     * @return void
     */
    public function testItCanCloneCollection(): void
    {
        $ori = new Collection([
            'one' => 'one',
            'two' => [
                'one',
                'two' => [1, 2],
            ],
            'three' => new Collection([]),
        ]);

        $clone = clone $ori;

        $ori->set('one', 'uno');
        $this->assertEquals('one', $clone->get('one'));

        $clone->set('one', 1);
        $this->assertEquals('uno', $ori->get('one'));
    }

    /**
     * Test it can get sum using reduce.
     *
     * @return void
     */
    public function testItCanGetSumUsingReduce(): void
    {
        $collection = new Collection([1, 2, 3, 4]);

        $sum = $collection->reduce(fn ($carry, $item) => $carry + $item);

        $this->assertTrue($sum === 10);
    }

    /**
     * Test it can get take first.
     *
     * @return void
     */
    public function testItCanGetTakeFirst(): void
    {
        $coll = new Collection([10, 20, 30, 40, 50, 60, 70, 80, 90]);

        $this->assertEquals([10, 20], $coll->take(2)->toArray());
    }

    /**
     * Test it can get take last.
     *
     * @return void
     */
    public function testItCanGetTakeLast(): void
    {
        $coll = new Collection([10, 20, 30, 40, 50, 60, 70, 80, 90]);

        $this->assertEquals([80, 90], $coll->take(-2)->toArray());
    }

    /**
     * Test it can push new item.
     *
     * @return void
     */
    public function testItCanPushNewItem(): void
    {
        $coll = new Collection([10, 20, 30, 40, 50, 60, 70, 80, 90]);
        $coll->push(100);

        $this->assertTrue(in_array(100, $coll->toArray()));
    }

    /**
     * Test it can get diff.
     *
     * @return void
     */
    public function testItCanGetDiff(): void
    {
        $coll = new Collection([1, 2, 3, 4, 5]);
        $coll->diff([2, 4, 6, 8]);

        $this->assertEquals([1, 3, 5], $coll->items());
    }

    /**
     * Test it can get diff using key.
     *
     * @return void
     */
    public function testItCanGetDiffUsingKey(): void
    {
        $coll = new Collection([
            'bau_1' => 'manga',
            'bau_2' => 'jeruk',
            'bau_3' => 'ap',
            'bau_4' => 'melon',
            'bau_5' => 'rambutan',
        ]);
        $coll->diffKeys([
            'bau_2' => 'orange',
            'bau_4' => 'water melon',
            'bau_6' => 'six',
            'bau_8' => 'eight',
        ]);

        $this->assertEquals([
            'bau_1' => 'manga',
            'bau_3' => 'ap',
            'bau_5' => 'rambutan',
        ], $coll->toArray());
    }

    /**
     * Test it can get diff using assoc.
     *
     * @return void
     */
    public function testItCanGetDiffUsingAssoc(): void
    {
        $coll = new Collection([
            'color'   => 'green',
            'type'    => 'library',
            'version' => 0,
        ]);
        $coll->diffAssoc([
            'color'   => 'orange',
            'type'    => 'framework',
            'version' => 10,
            'used'    => 100,
        ]);

        $this->assertEquals([
            'color'   => 'green',
            'type'    => 'library',
            'version' => 0,
        ], $coll->toArray());
    }

    /**
     * Test it can get complement.
     *
     * @return void
     */
    public function testItCanGetComplement(): void
    {
        $coll = new Collection([1, 2, 3, 4, 5]);
        $coll->complement([2, 4, 6, 8]);

        $this->assertEquals([6, 8], $coll->items());
    }

    /**
     * Test it can get complement using key.
     *
     * @return void
     */
    public function testItCanGetComplementUsingKey(): void
    {
        $coll = new Collection([
            'bau_1' => 'manga',
            'bau_2' => 'jeruk',
            'bau_3' => 'ap',
            'bau_4' => 'melon',
            'bau_5' => 'rambutan',
        ]);
        $coll->complementKeys([
            'bau_2' => 'orange',
            'bau_4' => 'water melon',
            'bau_6' => 'six',
            'bau_8' => 'eight',
        ]);

        $this->assertEquals([
            'bau_6' => 'six',
            'bau_8' => 'eight',
        ], $coll->toArray());
    }

    /**
     * Test it can get complement using assoc.
     *
     * @return void
     */
    public function testItCanGetComplementUsingAssoc(): void
    {
        $coll = new Collection([
            'color'   => 'green',
            'type'    => 'library',
            'version' => 0,
        ]);
        $coll->complementAssoc([
            'color'   => 'orange',
            'type'    => 'framework',
            'version' => 10,
            'used'    => 100,
        ]);

        $this->assertEquals([
            'color'   => 'orange',
            'type'    => 'framework',
            'version' => 10,
            'used'    => 100,
        ], $coll->toArray());
    }

    /**
     * Test it can get filtered using where.
     *
     * @return void
     */
    public function testItCanGetFilteredUsingWhere(): void
    {
        $data = [
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
            ['user' => 'user5', 'age' => 14],
        ];
        $equal = (new Collection($data))->where('age', '=', '13');
        $this->assertEquals([
            3 => ['user' => 'user4', 'age' => 13],
        ], $equal->toArray());

        $identical = (new Collection($data))->where('age', '===', 13);
        $this->assertEquals([
            3 => ['user' => 'user4', 'age' => 13],
        ], $identical->toArray());

        $notequal = (new Collection($data))->where('age', '!=', '13');
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            4       => ['user' => 'user5', 'age' => 14],
        ], $notequal->toArray());

        $notEqualIdentical = (new Collection($data))->where('age', '!==', 13);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            4       => ['user' => 'user5', 'age' => 14],
        ], $notEqualIdentical->toArray());

        $greatThan = (new Collection($data))->where('age', '>', 13);
        $this->assertEquals([
            4 => ['user' => 'user5', 'age' => 14],
        ], $greatThan->toArray());

        $greatThanEqual = (new Collection($data))->where('age', '>=', 13);
        $this->assertEquals([
            3 => ['user' => 'user4', 'age' => 13],
            4 => ['user' => 'user5', 'age' => 14],
        ], $greatThanEqual->toArray());

        $lessThan = (new Collection($data))->where('age', '<', 13);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
        ], $lessThan->toArray());

        $lessThanEqual = (new Collection($data))->where('age', '<=', 13);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
        ], $lessThanEqual->toArray());
    }

    /**
     * Test it can filter data using where in.
     *
     * @return void
     */
    public function testItCanFilterDataUsingWhereIn(): void
    {
        $data = [
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
            ['user' => 'user5', 'age' => 14],
        ];

        $wherein = (new Collection($data))->whereIn('age', [10, 12]);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
        ], $wherein->toArray());
    }

    /**
     * Test it can filter data using where not in.
     *
     * @return void
     */
    public function testItCanFilterDataUsingWhereNotIn(): void
    {
        $data = [
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
            ['user' => 'user4', 'age' => 13],
            ['user' => 'user5', 'age' => 14],
        ];

        $wherein = (new Collection($data))->whereNotIn('age', [13, 14]);
        $this->assertEquals([
            ['user' => 'user1', 'age' => 10],
            ['user' => 'user2', 'age' => 12],
            ['user' => 'user3', 'age' => 10],
        ], $wherein->toArray());
    }
}
