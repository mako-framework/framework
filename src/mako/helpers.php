<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako;

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