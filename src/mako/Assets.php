<?php

namespace mako;

use \mako\Config;
use \mako\assets\Container;

/**
 * Asset manager.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Assets
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Array of asset groups.
	 *
	 * @var array
	 */

	protected static $groups = array();

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
	 * Returns the path or URL of the assets.
	 *
	 * @access  public
	 * @return  string
	 */

	public static function location()
	{
		static $location;

		if($location === null)
		{
			$location = Config::get('application.asset_location');
		}

		return $location;
	}

	/**
	 * Returns the instance of the chosen asset group.
	 *
	 * @access  public
	 * @param   string                  $name  (optional) Group name
	 * @return  \mako\assets\Container
	 */

	public static function group($name = 'default')
	{
		if(!isset(static::$groups[$name]))
		{
			static::$groups[$name] = new Container();
		}

		return static::$groups[$name];
	}

	/**
	 * Magic shortcut to the default asset container instance.
	 *
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 * @return  mixed
	 */

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array(array(static::group(), $name), $arguments);
	}
}

/** -------------------- End of file -------------------- **/