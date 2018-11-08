<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\utility;

use mako\tests\TestCase;
use mako\utility\Collection;

/**
 * @group unit
 */
class CollectionTest extends TestCase
{
	/**
	 *
	 */
	public function testGetItems()
	{
		$collection = new Collection();

		$this->assertEquals([], $collection->getItems());

		//

		$collection = new Collection([1, 2, 3]);

		$this->assertEquals([1, 2, 3], $collection->getItems());
	}

	/**
	 *
	 */
	public function testPut()
	{
		$collection = new Collection();

		$collection->put('foo', 'bar');
		$collection->put(0, 'baz');

		$this->assertEquals('bar', $collection['foo']);
		$this->assertEquals('baz', $collection[0]);
	}

	/**
	 *
	 */
	public function testHas()
	{
		$collection = new Collection();

		$this->assertFalse($collection->has('foo'));

		$collection->put('foo', 'bar');

		$this->assertTrue($collection->has('foo'));
	}

	/**
	 *
	 */
	public function testHasNullValue()
	{
		$collection = new Collection(['foo' => null]);

		$this->assertTrue($collection->has('foo'));
	}

	/**
	 *
	 */
	public function testGet()
	{
		$collection = new Collection();

		$this->assertNull($collection->get('foo'));

		$this->assertFalse($collection->get('foo', false));

		$collection->put('foo', true);

		$this->assertTrue($collection->get('foo'));

		$this->assertTrue($collection->get('foo', false));
	}

	/**
	 *
	 */
	public function testGetNullValue()
	{
		$collection = new Collection(['foo' => null]);

		$this->assertNull($collection->get('foo', 'bar'));
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$collection = new Collection();

		$collection->put('foo', true);

		$this->assertTrue($collection->has('foo'));

		$collection->remove('foo');

		$this->assertFalse($collection->has('foo'));
	}

	/**
	 *
	 */
	public function testClear()
	{
		$collection = new Collection([1, 2, 3]);

		$this->assertEquals(3, $collection->count());

		$collection->clear();

		$this->assertEquals(0, $collection->count());
	}

	/**
	 *
	 */
	public function testOffsetExists()
	{
		$collection = new Collection();

		$this->assertFalse(isset($collection[0]));

		//

		$collection = new Collection([1, 2, 3]);

		$this->assertTrue(isset($collection[0]));
	}

	/**
	 *
	 */
	public function testOffsetGet()
	{
		$collection = new Collection([1, 2, 3]);

		$this->assertEquals(1, $collection[0]);
	}

	/**
	 *
	 */
	public function testOffsetGetNullValue()
	{
		$collection = new Collection(['foo' => null]);

		$this->assertNull($collection['foo']);
	}

	/**
	 * @expectedException \OutOfBoundsException
	 * @expectedExceptionMessage Undefined offset [ 0 ].
	 */
	public function testOffsetGetWithUndefinedOffset()
	{
		$collection = new Collection();

		$collection[0];
	}

	/**
	 *
	 */
	public function testOffsetSet()
	{
		$collection = new Collection();

		$collection[] = 'barfoo';

		$this->assertTrue(isset($collection[0]));

		$this->assertEquals('barfoo', $collection[0]);

		$collection[4] = 'foobar';

		$this->assertTrue(isset($collection[4]));

		$this->assertEquals('foobar', $collection[4]);
	}

	/**
	 *
	 */
	public function testOffsetUnset()
	{
		$collection = new Collection([1, 2, 3]);

		unset($collection[1]);

		$this->assertFalse(isset($collection[1]));
	}

	/**
	 *
	 */
	public function testCount()
	{
		$collection = new Collection();

		$this->assertEquals(0, count($collection));

		$this->assertEquals(0, $collection->count());

		//

		$collection = new Collection([1, 2, 3]);

		$this->assertEquals(3, count($collection));

		$this->assertEquals(3, $collection->count());
	}

	/**
	 *
	 */
	public function testIteration()
	{
		$string = '';

		foreach(new Collection([1, 2, 3]) as $item)
		{
			$string .= $item;
		}

		$this->assertEquals('123', $string);
	}

	/**
	 *
	 */
	public function testIsEmpty()
	{
		$collection = new Collection();

		$this->assertTrue($collection->isEmpty());

		//

		$collection = new Collection([1, 2, 3]);

		$this->assertFalse($collection->isEmpty());
	}

	/**
	 *
	 */
	public function testUnshift()
	{
		$collection = new Collection([1, 2, 3]);

		$count = $collection->unshift(10);

		$this->assertEquals(4, $count);

		$this->assertEquals([10, 1, 2, 3], $collection->getItems());
	}

	/**
	 *
	 */
	public function testShift()
	{
		$collection = new Collection([1, 2, 3]);

		$item = $collection->shift();

		$this->assertEquals(1, $item);

		$this->assertEquals([2, 3], $collection->getItems());
	}

	/**
	 *
	 */
	public function testPush()
	{
		$collection = new Collection([1, 2, 3]);

		$count = $collection->push(10);

		$this->assertEquals(4, $count);

		$this->assertEquals([1, 2, 3, 10], $collection->getItems());
	}

	/**
	 *
	 */
	public function testPop()
	{
		$collection = new Collection([1, 2, 3]);

		$item = $collection->pop();

		$this->assertEquals(3, $item);

		$this->assertEquals([1, 2], $collection->getItems());
	}

	/**
	 *
	 */
	public function testSort()
	{
		$collection = new Collection([2, 1, 3, 5, 6, 4]);

		$collection->sort(function($a, $b)
		{
			if($a == $b)
			{
				return 0;
			}

			return ($a < $b) ? -1 : 1;
		});

		$this->assertSame([1 => 1, 0 => 2, 2 => 3, 5 => 4, 3 => 5, 4 => 6], $collection->getItems());
	}

	/**
	 *
	 */
	public function testSortWithoutMaintainingIndexAssociation()
	{
		$collection = new Collection([2, 1, 3, 5, 6, 4]);

		$collection->sort(function($a, $b)
		{
			if($a == $b)
			{
				return 0;
			}

			return ($a < $b) ? -1 : 1;
		}, false);

		$this->assertSame([1, 2, 3, 4, 5, 6], $collection->getItems());
	}

	/**
	 *
	 */
	public function testChunk()
	{
		$collection = new Collection([1, 2, 3, 4, 5, 6]);

		$collection = $collection->chunk(2);

		$this->assertEquals(3, count($collection));

		$this->assertEquals([1, 2], $collection[0]->getItems());

		$this->assertEquals([3, 4], $collection[1]->getItems());

		$this->assertEquals([5, 6], $collection[2]->getItems());
	}

	/**
	 *
	 */
	public function testShuffle()
	{
		$collection = new Collection([1, 2]);

		$collection->shuffle();

		$this->assertTrue(($collection[0] === 1 || $collection[0] === 2));
	}

	/**
	 *
	 */
	public function testExtending()
	{
		Collection::extend('increaseByOne', function()
		{
			foreach($this->items as $key => $value)
			{
				$this->items[$key] += 1;
			}
		});

		$collection = new Collection([1, 2, 3, 4, 5, 6]);

		$collection->increaseByOne();

		$this->assertSame([2, 3, 4, 5, 6, 7], $collection->getItems());
	}

	/**
	 * @expectedException \BadMethodCallException
	 */
	public function testException()
	{
		$collection = new Collection();

		$collection->nope();
	}

	/**
	 *
	 */
	public function testResetKeys()
	{
		$collection = new Collection([1, 2, 3, 4, 5, 6]);

		unset($collection[2]);

		$this->assertEquals([0 => 1, 1 => 2, 3 => 4, 4 => 5, 5 => 6], $collection->getItems());

		$collection->resetKeys();

		$this->assertEquals([0 => 1, 1 => 2, 2 => 4, 3 => 5, 4 => 6], $collection->getItems());

	}

	/**
	 *
	 */
	public function testGetValues()
	{
		$collection = new Collection(['foo' => 'bar', 'baz' => 'bax']);

		$this->assertSame(['bar', 'bax'], $collection->getValues());
	}

	/**
	 *
	 */
	public function testEach()
	{
		$collection = new Collection([1, 2, 3]);

		$collection->each(function($value, $key)
		{
			return $key . ':' . $value;
		});

		$this->assertSame(['0:1', '1:2', '2:3'], $collection->getItems());
	}

	/**
	 *
	 */
	public function testMap()
	{
		$collection = new Collection([1, 2, 3]);

		$mapped = $collection->map(function($value)
		{
			return $value + 1;
		});

		$this->assertSame([2, 3, 4], $mapped->getItems());

		$this->assertSame([1, 2, 3], $collection->getItems());

		//

		$collection = new Collection(['first' => 1, 'second' => 2, 'third' => 3]);

		$mapped = $collection->map(function($value)
		{
			return $value + 1;
		});

		$this->assertSame(['first' => 2, 'second' => 3, 'third' => 4], $mapped->getItems());

		// With key passes as second argument

		$mapped = $collection->map(function($value, $key)
		{
			return $key . ':' . ($value + 1);
		});

		$this->assertSame(['first' => 'first:2', 'second' => 'second:3', 'third' => 'third:4'], $mapped->getItems());

		$this->assertSame(['first' => 1, 'second' => 2, 'third' => 3], $collection->getItems());
	}

	/**
	 *
	 */
	public function testFilter()
	{
		$collection = new Collection([1, null, 3]);

		$filtered = $collection->filter();

		$this->assertSame([1, 3], $filtered->getValues());

		$this->assertSame([1, null, 3], $collection->getValues());

		//

		$collection = new Collection([1, 2, 3]);

		$filtered = $collection->filter(function($value)
		{
			return $value !== 2;
		});

		$this->assertSame([1, 3], $filtered->getValues());

		$this->assertSame([1, 2, 3], $collection->getValues());

		//

		$collection = new Collection([1, 2, 3, 'foo' => 'bar', 4]);

		$filtered = $collection->filter(function($value, $key)
		{
			return is_int($key);
		});

		$this->assertSame([1, 2, 3, 4], $filtered->getValues());

		$this->assertSame([1, 2, 3, 'foo' => 'bar', 4], $collection->getItems());
	}

	/**
	 *
	 */
	public function testMerge()
	{
		$merged = (new Collection([1, 2, 3]))->merge(new Collection([4, 5, 6]));

		$this->assertEquals([1, 2, 3, 4, 5, 6], $merged->getItems());
	}
}
