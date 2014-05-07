<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\utility;

use \Closure;
use \ArrayIterator;

/**
 * Collection.
 * 
 * @author  Frederic G. Østby
 */

class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Collection items.
	 * 
	 * @var array
	 */

	protected $items = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

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

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

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
		if(is_null($offset))
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
	 * @param   mixex   $item  Collection item
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
	 * @param   \Closure  $comparator  Comparator closure
	 * @return  boolean
	 */

	public function sort(Closure $comparator)
	{
		return uasort($this->items, $comparator);
	}

	/**
	 * Chunks the collection into a collection containing $size sized collections.
	 * 
	 * @access  public
	 * @param   int
	 * @return  \mako\utility\Collection
	 */

	public function chunk($size)
	{
		$chunks = array_chunk($this->items, $size);

		$collections = [];

		foreach($chunks as $chunk)
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