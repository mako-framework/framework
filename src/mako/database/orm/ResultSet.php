<?php

namespace mako\database\orm;

use \mako\Arr;
use \ArrayIterator;

/**
 * ORM result set.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class ResultSet implements \ArrayAccess, \Countable, \IteratorAggregate
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Database results.
	 * 
	 * @var array
	 */

	protected $results = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   array   $results  Database results
	 */

	public function __construct(array $results = array())
	{
		$this->results = $results;
	}

	/**
	 * Clones all results when cloning the result set.
	 * 
	 * @access  public
	 */

	public function __clone()
	{
		foreach($this->results as $key => $value)
		{
			$this->results[$key] = clone $value;
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Checks whether or not an offset exists.
	 * 
	 * @access  public
	 * @param   mixed    $offset  The offset to check for
	 * @return  boolean
	 */

	public function offsetExists($offset)
	{
		return isset($this->results[$offset]);
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
		return isset($this->results[$offset]) ? $this->results[$offset] : null;
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
			$this->results[] = $value;
		}
		else
		{
			$this->results[$offset] = $value;
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
		unset($this->results[$offset]);
	}

	/**
	 * Returns the numner of items in the result set.
	 * 
	 * @access  public
	 * @return  int
	 */

	public function count()
	{
		return count($this->results);
	}

	/**
	 * Retruns an array iterator object.
	 * 
	 * @access  public
	 * @return  \ArrayIterator
	 */

	public function getIterator()
	{
		return new ArrayIterator($this->results);
	}

	/**
	 * Returns TRUE if the result set is empty and FALSE if not.
	 * 
	 * @return  boolean
	 */

	public function isEmpty()
	{
		return empty($this->results);
	}

	/**
	 * Returns an array containing only the values of chosen column.
	 * 
	 * @access  public
	 * @param   string  $column  Column name
	 * @return  array
	 */

	public function pluck($column)
	{
		return Arr::pluck($this->results, $column);
	}

	/**
	 * Returns an array representation of the result set.
	 * 
	 * @access  public
	 * @param   boolean  $protect  (optional) Protect columns?
	 * @param   boolean  $raw      (optional) Get raw values?
	 * @return  array
	 */

	public function toArray($protect = true, $raw = false)
	{
		$results = array();

		foreach($this->results as $result)
		{
			$results[] = $result->toArray($protect, $raw);
		}

		return $results;
	}

	/**
	 * Returns a json representation of the result set.
	 * 
	 * @access  public
	 * @param   boolean  $protect  (optional) Protect columns?
	 * @param   boolean  $raw      (optional) Get raw values?
	 * @return  string
	 */

	public function toJson($protect = true, $raw = false)
	{
		return json_encode($this->toArray($protect, $raw));
	}

	/**
	 * Returns a json representation of the result set.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function __toString()
	{
		return $this->toJson();
	}
}

/** -------------------- End of file -------------------- **/