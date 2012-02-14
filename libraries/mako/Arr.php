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
	* @param   array   Array we're going to search
	* @param   string  Array path
	* @param   mixed   Default return value
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
	* Returns a random value from an array.
	*
	* @access  public
	* @param   array   Array you want to pick a random value from
	* @return  mixed
	*/

	public static function random(array $array)
	{
		return $array[array_rand($array)];
	}
}

/** -------------------- End of file --------------------**/