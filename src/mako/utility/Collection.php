<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\utility;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;

/**
 * Collection.
 *
 * @author  Frederic G. Østby
 */

class Collection implements ArrayAccess, Countable, IteratorAggregate
{
	/**
	 * Collection items.
	 *
	 * @var array
	 */

	protected $items = [];

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   array   $items  Collection items
	 */

	public function __construct(array $items = [])
	{
		$this->items = $items;
	}

	/**
	 * Returns all the items in the collection.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getItems()
	{
		return $this->items;
	}

	/**
	 * Checks whether or not an offset exists.
	 *
	 * @access  public
	 * @param   mixed    $offset  The offset to check for
	 * @return  boolean
	 */

	public function offsetExists($offset)
	{
		return isset($this->items[$offset]);
	}

	/**
	 * Returns the value at the specified offset.
	 *
	 * @access  public
	 * @param   mixed   $offset  The offset to retrieve
	 * @return  mixed
	 */

	public function offsetGet($offset)
	{
		return isset($this->items[$offset]) ? $this->items[$offset] : null;
	}

	/**
	 * Assigns a value to the specified offset.
	 *
	 * @access  public
	 * @param   mixed   $offset  The offset to assign the value to
	 * @param   mixed   $value   The value to set
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
	 * @access  public
	 * @param   mixed   $offset  The offset to unset
	 */

	public function offsetUnset($offset)
	{
		unset($this->items[$offset]);
	}

	/**
	 * Returns the numner of items in the collection.
	 *
	 * @access  public
	 * @return  int
	 */

	public function count()
	{
		return count($this->items);
	}

	/**
	 * Retruns an array iterator object.
	 *
	 * @access  public
	 * @return  \ArrayIterator
	 */

	public function getIterator()
	{
		return new ArrayIterator($this->items);
	}

	/**
	 * Returns TRUE if the collection is empty and FALSE if not.
	 *
	 * @return  boolean
	 */

	public function isEmpty()
	{
		return empty($this->items);
	}

	/**
	 * Prepends the passed item to the front of the collection
	 * and returns the new number of elements in the collection.
	 *
	 * @access  public
	 * @param   mixed   $item  Collection item
	 * @return  int
	 */

	public function unshift($item)
	{
		return array_unshift($this->items, $item);
	}

	/**
	 * Shifts the first value of the collection off and returns it,
	 * shortening the collection by one element.
	 *
	 * @access  public
	 * @return  mixed
	 */

	public function shift()
	{
		return array_shift($this->items);
	}

	/**
	 * Pushes the passed variable onto the end of the collection
	 * and returns the new number of elements in the collection.
	 *
	 * @access  public
	 * @param   mixed   $item  Collection item
	 * @return  int
	 */

	public function push($item)
	{
		return array_push($this->items, $item);
	}

	/**
	 * Pops and returns the last value of the collection,
	 * shortening the collection by one element.
	 *
	 * @access  public
	 * @return  mixed
	 */

	public function pop()
	{
		return array_pop($this->items);
	}

	/**
	 * Sorts the collection using the specified comparator closure
	 * and returns TRUE on success and FALSE on failure.
	 *
	 * @access  public
	 * @param   \Closure  $comparator                Comparator closure
	 * @param   boolean   $maintainIndexAssociation  Maintain index association?
	 * @return  boolean
	 */

	public function sort(Closure $comparator, $maintainIndexAssociation = true)
	{
		return $maintainIndexAssociation ? uasort($this->items, $comparator) : usort($this->items, $comparator);
	}

	/**
	 * Chunks the collection into a collection containing $size sized collections.
	 *
	 * @access  public
	 * @param   int                       $size  Chunk size
	 * @return  \mako\utility\Collection
	 */

	public function chunk($size)
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
	 * @access  public
	 * @return  boolean
	 */

	public function shuffle()
	{
		return shuffle($this->items);
	}
}
