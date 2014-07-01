<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako;

/**
 * Returns the Mako environment. NULL is returned if no environment is specified.
 * 
 * @return  string|null
 */

function get_env()
{
	return getenv('MAKO_ENV') ?: null;
}

/**
 * Returns path to a package or application directory.
 *
 * @param   string  $parentPath    Parent path
 * @param   string  $relativePath  Relative path
 * @param   string  $file          File
 * @param   string  $ext           (optional) File extension
 * @return  string
 */

function get_path($parentPath, $relativePath, $file, $ext = '.php')
{
	if(strpos($file, '::') !== false)
	{
		list($package, $file) = explode('::', $file);

		$path = $parentPath . '/packages/' . $package . '/' . $relativePath . '/' . $file . $ext;
	}
	else
	{
		$path = $parentPath . '/' . $relativePath . '/' . $file . $ext;
	}

	return $path;
}

/**
 * Returns an array of cascading paths to a package or application directory.
 *
 * @param   string  $parentPath    Parent path
 * @param   string  $relativePath  Relative path
 * @param   string  $file          String
 * @param   string  $ext          (optional) File extension
 * @return  array
 */

function get_cascading_paths($parentPath, $relativePath, $file, $ext = '.php')
{
	$paths = [];

	if(strpos($file, '::') !== false)
	{
		list($package, $file) = explode('::', $file);

		$paths[] = $parentPath . '/' . $relativePath . '/packages/' . $package . '/' . $file . $ext;

		$paths[] = $parentPath . '/packages/' . $package . '/' . $relativePath . '/' . $file . $ext;
	}
	else
	{
		$paths[] = $parentPath . '/' . $relativePath . '/' . $file . $ext;
	}

	return $paths;
}

/**
 * Returns an array of all traits used by a class.
 * 
 * @param   string|object  $class     Class name or class instance
 * @param   boolean        $autoload  (optional) Autoload
 * @return  array
 */

function get_class_traits($class, $autoload = true)
{
	// Fetch all traits used by a class and its parents

	$traits = [];

	do
	{
		$traits += class_uses($class, $autoload);
	}
	while($class = get_parent_class($class));

	// Find all traits used by the traits

	$search = $traits;

	$searched = [];

	while(!empty($search))
	{
		$trait = array_pop($search);

		if(isset($searched[$trait]))
		{
			continue;
		}

		$traits += $search += class_uses($trait, $autoload);

		$searched[$trait] = $trait;
	}

	// Return complete list of traits used by the class

	return $traits;
}