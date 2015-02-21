<?php

namespace mako\tests\unit\utility;

use mako\utility\Collection;

/**
 * @group unit
 */

class CollectionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testGetItems()
	{
		$collection = new Collection();

		$this->assertEquals([], $collection->getItems());

		//

		$collection = new Collection([1,2,3]);

		$this->assertEquals([1,2,3], $collection->getItems());
	}

	/**
	 *
	 */

	public function testOffsetExists()
	{
		$collection = new Collection();

		$this->assertFalse(isset($collection[0]));

		//

		$collection = new Collection([1,2,3]);

		$this->assertTrue(isset($collection[0]));
	}

	/**
	 *
	 */

	public function testOffsetGet()
	{
		$collection = new Collection();

		$this->assertNull($collection[0]);

		//

		$collection = new Collection([1,2,3]);

		$this->assertEquals(1, $collection[0]);
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
		$collection = new Collection([1,2,3]);

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

		$collection = new Collection([1,2,3]);

		$this->assertEquals(3, count($collection));

		$this->assertEquals(3, $collection->count());
	}

	/**
	 *
	 */

	public function testIteration()
	{
		$string = '';

		foreach(new Collection([1,2,3]) as $item)
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
}