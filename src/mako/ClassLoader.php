<?php

namespace mako;

/**
 * Classloader.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class ClassLoader
{
	//---------------------------------------------
	// Class properties
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

	protected static $directories = array
	(
		MAKO_APPLICATION_PARENT_PATH,
		MAKO_PACKAGES_PATH,
	);

	/**
	 * Registered namespaces.
	 *
	 * @var array
	 */

	protected static $namespaces = array();

	/**
	 * Class aliases.
	 *
	 * @var array
	 */

	protected static $aliases = array();
	
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
	 * Registers the autoloader.
	 * 
	 * @access  public
	 */

	public static function register()
	{
		include realpath(__DIR__ . '/../../../../autoload.php');

		spl_autoload_register('mako\ClassLoader::load');
	}

	/**
	 * Add class to mapping.
	 *
	 * @access  public
	 * @param   string  $className  Class name
	 * @param   string  $classPath  Full path to class
	 */

	public static function mapClass($className, $classPath)
	{
		static::$classes[$className] = $classPath;
	}

	/**
	 * Add multiple classes to mapping.
	 *
	 * @access  public
	 * @param   array   $classes  Array of classes to map (key = class name and value = class path)
	 */

	public static function mapClasses(array $classes)
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
	 * @param   string  $path  Path to PSR-0 directory
	 */

	public static function directory($path)
	{
		static::$directories[] = rtrim($path, '/');
	}

	/**
	 * Registers a namespace.
	 *
	 * @access  public
	 * @param   string  $namespace  Namespace
	 * @param   string  $path       Path
	 */

	public static function registerNamespace($namespace, $path)
	{
		static::$namespaces[trim($namespace, '\\') . '\\'] = rtrim($path, '/');
	}

	/**
	 * Set an alias for a class.
	 *
	 * @access  public
	 * @param   string  $alias      Class alias
	 * @param   string  $className  Class name
	 */

	public static function alias($alias, $className)
	{
		static::$aliases[$alias] = $className;
	}

	/**
	 * Try to load a PSR-0 compatible class.
	 *
	 * @access  protected
	 * @param   string     $className  Class name
	 * @param   string     $directory  (Optional) Overrides the array of PSR-0 paths
	 * @return  boolean
	 */

	protected static function loadPSR0($className, $directory = null)
	{
		$classPath = '';

		if(($pos = strripos($className, '\\')) !== false)
		{
			$namespace = substr($className, 0, $pos);
			$className = substr($className, $pos + 1);
			$classPath = str_replace('\\', '/', $namespace) . '/';
		}

		$classPath .= str_replace('_', '/', $className) . '.php';

		$directories = ($directory === null) ? static::$directories : array($directory);

		foreach($directories as $directory)
		{
			if(file_exists($directory . '/' . $classPath))
			{
				include($directory . '/' . $classPath);

				return true;
			}	
		}

		return false;
	}

	/**
	 * Autoloader.
	 *
	 * @access  public
	 * @param   string   $className  Class name
	 * @return  boolean
	 */

	public static function load($className)
	{
		$className = ltrim($className, '\\');

		// Try to autoload an aliased class

		if(isset(static::$aliases[$className]))
		{
			return class_alias(static::$aliases[$className], $className);
		}
		
		// Try to load a mapped class

		if(isset(static::$classes[$className]))
		{
			include static::$classes[$className];

			return true;
		}

		// Try to load class from a registered namespace

		foreach(static::$namespaces as $namespace => $path)
		{
			if(strpos($className, $namespace) === 0)
			{
				if(static::loadPSR0(substr($className, strlen($namespace)), $path))
				{
					return true;
				}
			}
		}

		// Try to load a PSR-0 compatible class
		// The second call to the loadPSR0 method is used to autoload legacy code

		if(static::loadPSR0($className) || static::loadPSR0(strtolower($className)))
		{
			return true;
		}

		return false;
	}
}

/** -------------------- End of file -------------------- **/