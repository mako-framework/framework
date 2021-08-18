<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use mako\common\traits\ExtendableTrait;
use OutOfBoundsException;

use function array_chunk;
use function array_combine;
use function array_diff_key;
use function array_filter;
use function array_flip;
use function array_intersect_key;
use function array_key_exists;
use function array_key_first;
use function array_key_last;
use function array_keys;
use function array_map;
use function array_merge;
use function array_pop;
use function array_push;
use function array_shift;
use function array_unshift;
use function array_values;
use function count;
use function shuffle;
use function uasort;
use function usort;
use function vsprintf;

/**
 * Collection.
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
	 * @param array $items Collection items
	 */
	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	/**
	 * Returns all the items in the collection.
	 *
	 * @return array
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * Returns all the values in the collection.
	 *
	 * @return array
	 */
	public function getValues(): array
	{
		return array_values($this->items);
	}

	/**
	 * Resets the collection keys.
	 *
	 * @return $this
	 */
	public function resetKeys()
	{
		$this->items = array_values($this->items);

		return $this;
	}

	/**
	 * Adds a new item to the collection.
	 *
	 * @param  int|string $key   Key
	 * @param  mixed      $value Value
	 * @return $this
	 */
	public function put($key, $value)
	{
		$this->items[$key] = $value;

		return $this;
	}

	/**
	 * Returns TRUE if the item key exists and FALSE if not.
	 *
	 * @param  int|string $key Key
	 * @return bool
	 */
	public function has($key): bool
	{
		return array_key_exists($key, $this->items);
	}

	/**
	 * Returns an item from the collection.
	 *
	 * @param  int|string $key     Key
	 * @param  mixed      $default Default value
	 * @return mixed
	 */
	public function get($key, $default = null)
	{
		if(array_key_exists($key, $this->items))
		{
			return $this->items[$key];
		}

		return $default;
	}

	/**
	 * Removes an item from the collection.
	 *
	 * @param  int|string $key Key
	 * @return $this
	 */
	public function remove($key)
	{
		unset($this->items[$key]);

		return $this;
	}

	/**
	 * Clears the collection.
	 *
	 * @return $this
	 */
	public function clear()
	{
		$this->items = [];

		return $this;
	}

	/**
	 * Checks whether or not an offset exists.
	 *
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
	 * @param  mixed $offset The offset to retrieve
	 * @return mixed
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet($offset)
	{
		if(array_key_exists($offset, $this->items))
		{
			return $this->items[$offset];
		}

		throw new OutOfBoundsException(vsprintf('Undefined offset [ %s ].', [$offset]));
	}

	/**
	 * Assigns a value to the specified offset.
	 *
	 * @param mixed $offset The offset to assign the value to
	 * @param mixed $value  The value to set
	 */
	public function offsetSet($offset, $value): void
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
	 * @param mixed $offset The offset to unset
	 */
	public function offsetUnset($offset): void
	{
		unset($this->items[$offset]);
	}

	/**
	 * Returns the numner of items in the collection.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * Retruns an array iterator object.
	 *
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
	 * @return mixed
	 */
	public function pop()
	{
		return array_pop($this->items);
	}

	/**
	 * Returns the first item of the collection or NULL if the collection is empty.
	 *
	 * @return mixed
	 */
	public function first()
	{
		$key = array_key_first($this->items);

		if($key === null)
		{
			return null;
		}

		return $this->items[$key];
	}

	/**
	 * Returns the last item of the collection or NULL if the collection is empty.
	 *
	 * @return mixed
	 */
	public function last()
	{
		$key = array_key_last($this->items);

		if($key === null)
		{
			return null;
		}

		return $this->items[$key];
	}

	/**
	 * Sorts the collection using the specified comparator callable
	 * and returns TRUE on success and FALSE on failure.
	 *
	 * @param  callable $comparator               Comparator callable
	 * @param  bool     $maintainIndexAssociation Maintain index association?
	 * @return $this
	 */
	public function sort(callable $comparator, bool $maintainIndexAssociation = true)
	{
		if($maintainIndexAssociation)
		{
			uasort($this->items, $comparator);
		}
		else
		{
			usort($this->items, $comparator);
		}

		return $this;
	}

	/**
	 * Chunks the collection into a collection containing $size sized collections.
	 *
	 * @param  int    $size Chunk size
	 * @return static
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
	 * @return $this
	 */
	public function shuffle()
	{
		shuffle($this->items);

		return $this;
	}

	/**
	 * Applies the callable on all items in the collection.
	 *
	 * @param  callable $callable Callable
	 * @return $this
	 */
	public function each(callable $callable)
	{
		foreach($this->items as $key => $value)
		{
			$this->items[$key] = $callable($value, $key);
		}

		return $this;
	}

	/**
	 * Returns a new collection where the callable has
	 * been applied to all the items.
	 *
	 * @param  callable $callable Callable
	 * @return static
	 */
	public function map(callable $callable)
	{
		$keys = array_keys($this->items);

		$values = array_map($callable, $this->items, $keys);

		return new static(array_combine($keys, $values));
	}

	/**
	 * Returns a new filtered collection.
	 *
	 * @param  callable|null $callable Filter
	 * @return static
	 */
	public function filter(?callable $callable = null)
	{
		if($callable === null)
		{
			return new static(array_filter($this->items));
		}

		return new static(array_filter($this->items, $callable, ARRAY_FILTER_USE_BOTH));
	}

	/**
	 * Returns a new collection where all items not in the provided list have been removed.
	 *
	 * @param  array  $keys Keys
	 * @return static
	 */
	public function with(array $keys)
	{
		return new static(array_intersect_key($this->items, array_flip($keys)));
	}

	/**
	 * Returns a new collection where all items in the provided list have been removed.
	 *
	 * @param  array  $keys Keys
	 * @return static
	 */
	public function without(array $keys)
	{
		return new static(array_diff_key($this->items, array_flip($keys)));
	}

	/**
	 * Merges two collections.
	 *
	 * @param  \mako\utility\Collection $collection Collection to merge
	 * @return static
	 */
	public function merge(Collection $collection)
	{
		return new static(array_merge($this->items, $collection->getItems()));
	}
}
