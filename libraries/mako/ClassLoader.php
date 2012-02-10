<?php

namespace mako;

/**
* Classloader.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class ClassLoader
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Mapping from class names to paths.
	*
	* @var array
	*/

	protected static $classes = array();

	/**
	* PSR-0 directories.
	*
	* @var array
	*/

	protected static $psr0 = array
	(
		MAKO_LIBRARIES_PATH
	);
	
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
	* Add class to mapping.
	*
	* @access  public
	* @param   string  Class name
	* @param   string  Full path to class
	*/

	public static function addClass($className, $classPath)
	{
		static::$classes[$className] = $classPath;
	}

	/**
	* Add multiple classes to mapping.
	*
	* @access  public
	* @param   array   Array of classes to map (key = class name and value = class path)
	*/

	public static function addClasses(array $classes)
	{
		foreach($classes as $name => $path)
		{
			static::$classes[$name] = $path;
		}
	}

	/**
	* Adds a PSR-0 directory path.
	*
	* @access  public
	* @param   string  Path to PSR-0 directory
	*/

	public static function addPsr($path)
	{
		static::$psr0[] = $path;
	}

	/**
	* Autoloader.
	*
	* @access  public
	* @param   string   Class name
	* @return  boolean
	*/

	public static function load($className)
	{
		$className = ltrim($className, '\\');
		
		// Try to load a mapped class

		if(isset(static::$classes[$className]) && file_exists(static::$classes[$className]))
		{
			include static::$classes[$className];

			return true;
		}

		// Try to load an application class

		$fileName = MAKO_APPLICATION_PATH . '/' . str_replace('\\', '/', strtolower($className)) . '.php';

		if(file_exists($fileName))
		{
			include $fileName;

			return true;
		}

		// Try to load class from a PSR-0 compatible library

		$fileName  = '';
		$namespace = '';

		if($lastNsPos = strripos($className, '\\'))
		{
			$namespace = substr($className, 0, $lastNsPos);
			$className = substr($className, $lastNsPos + 1);
			$fileName  = str_replace('\\', '/', $namespace) . '/';
		}

		$fileName .= str_replace('_', '/', $className) . '.php';

		foreach(static::$psr0 as $path)
		{
			if(file_exists($path . '/' . $fileName))
			{
				include($path . '/' . $fileName);

				return true;
			}	
		}

		return false;
	}
}

/** -------------------- End of file --------------------**/