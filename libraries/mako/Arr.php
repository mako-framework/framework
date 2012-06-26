<?php

namespace mako;

/**
* Array helper.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Arr
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Protected constructor since this is a static class.
	*
	* @access  protected
	*/

	protected function __construct()
	{
		// Nothing here
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Returns value from array using "dot notation".
	*
	* @access  public
	* @param   array   $array    Array we're going to search
	* @param   string  $path     Array path
	* @param   mixed   $default  Default return value
	* @return  mixed
	*/
	public static function get(array $array, $path, $default = null)
	{
		$segments = explode('.', $path);

		foreach($segments as $segment)
		{
			if(!is_array($array) || !isset($array[$segment]))
			{
				return $default;
			}

			$array = $array[$segment];
		}

		return $array;
	}

	/**
	* Sets an array value using "dot notation".
	*
	* @access  public
	* @param   array    $array  Array you want to modify
	* @param   string   $path   Array path
	* @param   mixed    $value  Value to set
	*/

	public static function set(array & $array, $path, $value)
	{
		$segments = explode('.', $path);

		while(count($segments) > 1)
		{
			$segment = array_shift($segments);

			if (!isset($array[$segment]) || !is_array($array[$segment]))
			{
				$array[$segment] = array();
			}

			$array =& $array[$segment];
		}

		$array[array_shift($segments)] = $value;
	}

	/**
	* Deletes an array value using "dot notation".
	*
	* @access  public
	* @param   array    $array  Array you want to modify
	* @param   string   $path   Array path
	*/

	public static function delete(array & $array, $path)
	{
		$segments = explode('.', $path);
		
		while(count($segments) > 1)
		{
			$segment = array_shift($segments);

			if (!isset($array[$segment]) || ! is_array($array[$segment]))
			{
				return false;
			}

			$array =& $array[$segment];
		}

		unset($array[array_shift($segments)]);

		return true;
	}

	/**
	* Returns a random value from an array.
	*
	* @access  public
	* @param   array   $array  Array you want to pick a random value from
	* @return  mixed
	*/

	public static function random(array $array)
	{
		return $array[array_rand($array)];
	}
}

/** -------------------- End of file --------------------**/