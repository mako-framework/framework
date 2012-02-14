<?php

namespace mako;

use \RuntimeException;

/**
* Package class.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Package
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
	* Initializes a package.
	*
	* @access  public
	* @param   string  Package name
	*/

	public static function init($name)
	{
		$path = MAKO_PACKAGES . '/' . $name . '/_init.php';

		if(!file_exists($path))
		{
			throw new RuntimeException(vsprintf("%s(): Unable to initialize the '%s' package. Make sure that it has been installed.", array(__METHOD__, $name)));
		}

		include_once $path;
	}

	/**
	* Returns path to a package or application directory.
	*
	* @access  public
	* @param   string  Path
	* @param   string  String
	* @return  string
	*/

	public static function path($path, $string)
	{
		if(strpos($string, '::') !== false)
		{
			list($package, $file) = explode('::', $string);

			$path = MAKO_PACKAGES . '/' . $package . '/' . $path . '/' . $file . '.php';
		}
		else
		{
			$path = MAKO_APPLICATION . '/' . $path . '/' . $string . '.php';
		}

		return $path;
	}
}