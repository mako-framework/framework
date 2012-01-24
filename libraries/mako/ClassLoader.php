<?php

namespace mako
{
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
		*/

		protected static $classes = array();
		
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
		* Load a class that has been mapped.
		*
		* @access  public
		* @param   string   Class name
		* @return  boolean
		*/

		public static function load($className)
		{
			if(isset(static::$classes[$className]))
			{
				if(file_exists(static::$classes[$className]))
				{
					include static::$classes[$className];

					return true;
				}
			}

			return false;
		}

		/**
		* Autoloader.
		*
		* @access  public
		* @param   string   Class name
		* @return  boolean
		*/

		public static function autoLoad($className)
		{
			// Try to load a mapped class

			if(static::load($className))
			{
				return true;
			}

			// Try to load an application class

			$fileName = MAKO_APPLICATION_PATH . '/' . str_replace('\\', '/', mb_strtolower($className)) . '.php';

			if(file_exists($fileName))
			{
				include $fileName;

				return true;
			}

			// Try to load class from a PSR-0 compatible library

			$className = ltrim($className, '\\');

			$fileName  = '';
			$namespace = '';

			if($lastNsPos = strripos($className, '\\'))
			{
				$namespace = substr($className, 0, $lastNsPos);
				$className = substr($className, $lastNsPos + 1);
				$fileName  = str_replace('\\', '/', $namespace) . '/';
			}

			$fileName .= str_replace('_', '/', $className) . '.php';

			if(file_exists(MAKO_LIBRARIES_PATH . '/' . $fileName))
			{
				include(MAKO_LIBRARIES_PATH . '/' . $fileName);

				return true;
			}

			return false;
		}
	}
}

/** -------------------- End of file --------------------**/