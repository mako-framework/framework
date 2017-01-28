<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;

use mako\common\ExtendableTrait;

/**
 * Collection.
 *
 * @author Frederic G. Østby
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate
{
	use ExtendableTrait;

	/**
	 * Collection items.
	 *
	 * @var array
	 */
	protected $items = [];

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param array $items Collection items
	 */
	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	/**
	 * Returns all the items in the collection.
	 *
	 * @access public
	 * @return array
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * Returns all the values in the collection.
	 *
	 * @access public
	 * @return array
	 */
	public function getValues(): array
	{
		return array_values($this->items);
	}

	/**
	 * Resets the collection keys.
	 *
	 * @access public
	 */
	public function resetKeys()
	{
		$this->items = array_values($this->items);
	}

	/**
	 * Adds a new item to the collection.
	 *
	 * @access public
	 * @param int|string $key   Key
	 * @param mixed      $value Value
	 */
	public function put($key, $value)
	{
		$this->items[$key] = $value;
	}

	/**
	 * Returns TRUE if the item key exists and FALSE if not.
	 *
	 * @access public
	 * @param  int|string $key Key
	 * @return bool
	 */
	public function has($key): bool
	{
		return isset($this->items[$key]);
	}

	/**
	 * Returns an item from the collection.
	 *
	 * @access public
	 * @param  int|string $key     Key
	 * @param  null|mixed $default Default value
	 * @return null|mixed
	 */
	public function get($key, $default = null)
	{
		return $this->items[$key] ?? $default;
	}

	/**
	 * Removes an item from the collection.
	 *
	 * @access public
	 * @param int|string $key Key
	 */
	public function remove($key)
	{
		unset($this->items[$key]);
	}

	/**
	 * Clears the collection.
	 *
	 * @access public
	 */
	public function clear()
	{
		$this->items = [];
	}

	/**
	 * Checks whether or not an offset exists.
	 *
	 * @access public
	 * @param  mixed $offset The offset to check for
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->items[$offset]);
	}

	/**
	 * Returns the value at the specified offset.
	 *
	 * @access public
	 * @param  mixed $offset The offset to retrieve
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		if(isset($this->items[$offset]))
		{
			return $this->items[$offset];
		}

		throw new OutOfBoundsException(vsprintf("%s(): Undefined offset [ %s ].", [__METHOD__, $offset]));
	}

	/**
	 * Assigns a value to the specified offset.
	 *
	 * @access public
	 * @param mixed $offset The offset to assign the value to
	 * @param mixed $value  The value to set
	 */
	public function offsetSet($offset, $value)
	{
		if($offset === null)
		{
			$this->items[] = $value;
		}
		else
		{
			$this->items[$offset] = $value;
		}
	}

	/**
	 * Unsets an offset.
	 *
	 * @access public
	 * @param mixed $offset The offset to unset
	 */
	public function offsetUnset($offset)
	{
		unset($this->items[$offset]);
	}

	/**
	 * Returns the numner of items in the collection.
	 *
	 * @access public
	 * @return int
	 */
	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * Retruns an array iterator object.
	 *
	 * @access public
	 * @return \ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	/**
	 * Returns TRUE if the collection is empty and FALSE if not.
	 *
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return empty($this->items);
	}

	/**
	 * Prepends the passed item to the front of the collection
	 * and returns the new number of elements in the collection.
	 *
	 * @access public
	 * @param  mixed $item Collection item
	 * @return int
	 */
	public function unshift($item): int
	{
		return array_unshift($this->items, $item);
	}

	/**
	 * Shifts the first value of the collection off and returns it,
	 * shortening the collection by one element.
	 *
	 * @access public
	 * @return mixed
	 */
	public function shift()
	{
		return array_shift($this->items);
	}

	/**
	 * Pushes the passed variable onto the end of the collection
	 * and returns the new number of elements in the collection.
	 *
	 * @access public
	 * @param  mixed $item Collection item
	 * @return int
	 */
	public function push($item): int
	{
		return array_push($this->items, $item);
	}

	/**
	 * Pops and returns the last value of the collection,
	 * shortening the collection by one element.
	 *
	 * @access public
	 * @return mixed
	 */
	public function pop()
	{
		return array_pop($this->items);
	}

	/**
	 * Sorts the collection using the specified comparator closure
	 * and returns TRUE on success and FALSE on failure.
	 *
	 * @access public
	 * @param  \Closure $comparator               Comparator closure
	 * @param  bool     $maintainIndexAssociation Maintain index association?
	 * @return bool
	 */
	public function sort(Closure $comparator, bool $maintainIndexAssociation = true): bool
	{
		return $maintainIndexAssociation ? uasort($this->items, $comparator) : usort($this->items, $comparator);
	}

	/**
	 * Chunks the collection into a collection containing $size sized collections.
	 *
	 * @access public
	 * @param  int                      $size Chunk size
	 * @return \mako\utility\Collection
	 */
	public function chunk(int $size)
	{
		$collections = [];

		foreach(array_chunk($this->items, $size) as $chunk)
		{
			$collections[] = new static($chunk);
		}

		return new static($collections);
	}

	/**
	 * Shuffles the items in the collection and returns
	 * TRUE on success and FALSE on failure.
	 *
	 * @access public
	 * @return bool
	 */
	public function shuffle(): bool
	{
		return shuffle($this->items);
	}

	/**
	 * Applies the callable on all items in the collection.
	 *
	 * @access public
	 * @param callable $callable Callable
	 */
	public function each(callable $callable)
	{
		foreach($this->items as $key => $value)
		{
			$this->items[$key] = $callable($value, $key);
		}
	}

	/**
	 * Returns a new collection where the callable has
	 * been applied to all the items.
	 *
	 * @access public
	 * @param  callable                 $callable Callable
	 * @return \mako\utility\Collection
	 */
	public function map(callable $callable)
	{
		return new static(array_map($callable, $this->items));
	}

	/**
	 * Returns a new filtered collection.
	 *
	 * @access public
	 * @param  callable                 $callable Filter
	 * @return \mako\utility\Collection
	 */
	public function filter(callable $callable = null)
	{
		if($callable === null)
		{
			return new static(array_filter($this->items));
		}

		return new static(array_filter($this->items, $callable));
	}
}
