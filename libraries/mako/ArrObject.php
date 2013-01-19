<?php

namespace mako;

use \mako\String;
use \RuntimeException;

class ArrObjectException extends RuntimeException{}

/**
 * Class wrapper for the arrays that allows to get values by camelCase properties of the object.
 */

class ArrObject
{

	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Holds the array passed to the constructor.
	 *
	 * @var array
	 */

	protected static $array;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   array   $array  Array to wrap
	 */

	public function __construct(array $array)
	{
		static::$array = $array;
	}

	/**
	 * Returns the wrapped array.
	 *
	 * @access  public
	 * @return  array
	 */

	public function toArray()
	{
		return static::$array;
	}

	/**
	 * Setter method that sets value of the array
	 *
	 * @access  public
	 * @param   string  $name   Variable name
	 * @param   mixed   $value  Variable value
	 */

	public function __set($name, $value)
	{
		$name = String::camel2underscored($name);

		static::$array[$name] = $value;
	}

	/**
	 * Getter method that gets value of the array
	 *
	 * @access  public
	 * @param   string  $name  Variable name
	 * @return  mixed
	 */

	public function __get($name)
	{
		$name = String::camel2underscored($name);

		if(isset(static::$array[$name]))
		{
			return static::$array[$name];
		}
	}
}

/** -------------------- End of file --------------------**/