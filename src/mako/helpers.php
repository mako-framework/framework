<?php

/**
 * Returns the Mako environment. NULL is returned if no environment is specified.
 * 
 * @return  mixed
 */

function mako_env()
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

function mako_path($parentPath, $relativePath, $file, $ext = '.php')
{
        if(strpos($file, '::') !== false)
        {
                list($package, $file) = explode('::', $file);

                $path = $parentPath . '/' . $package . '/' . $relativePath . '/' . $file . $ext;
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

function mako_cascading_paths($parentPath, $relativePath, $file, $ext = '.php')
{
	$paths = [];

	if(strpos($file, '::') !== false)
	{
		list($package, $file) = explode('::', $file);

		$paths[] = $parentPath . '/' . $relativePath . '/packages/' . $package . '/' . $file . $ext;

		$paths[] = $parentPath . '/' . $package . '/' . $relativePath . '/' . $file . $ext;
	}
	else
	{
		$paths[] = $parentPath . '/' . $relativePath . '/' . $file . $ext;
	}

	return $paths;
}