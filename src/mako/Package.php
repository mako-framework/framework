<?php

namespace mako;

use \RuntimeException;

/**
 * Package class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Package
{
	//---------------------------------------------
	// Class properties
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
	* @param   string  $name  Package name
	*/

	public static function init($name)
	{
		$path = MAKO_PACKAGES_PATH . '/' . $name . '/_init.php';

		if(!file_exists($path))
		{
			throw new RuntimeException(vsprintf("%s(): Unable to initialize the '%s' package. Make sure that it has been installed.", array(__METHOD__, $name)));
		}

		include_once $path;
	}

	/**
	* Returns TRUE if package is installed and FALSE if not.
	*
	* @access  public
	* @param   string   $name  Package name
	* @return  boolean
	*/

	public static function installed($name)
	{
		return is_dir(MAKO_PACKAGES_PATH . '/' . $name);
	}

	/**
	* Returns info about a package. FALSE is returned if no composer.json file is found.
	*
	* @access  public
	* @param   string   $name     Package name
	* @param   boolean  $asArray  (optional) Return as array?
	* @return  object
	*/

	public static function info($name, $asArray = false)
	{
		$path = MAKO_PACKAGES_PATH . '/' . $name . '/composer.json';

		if(file_exists($path))
		{
			return json_decode(file_get_contents($path), $asArray);
		}

		return false;
	}
}

/** -------------------- End of file -------------------- **/