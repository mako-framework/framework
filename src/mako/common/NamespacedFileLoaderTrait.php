<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\common;

use RuntimeException;

/**
 * Namespaced file loader trait.
 *
 * @author  Frederic G. Østby
 */

trait NamespacedFileLoaderTrait
{
	/**
	 * Default path.
	 *
	 * @var string
	 */

	protected $path;

	/**
	 * File extension.
	 *
	 * @var string
	 */

	protected $extension = '.php';

	/**
	 * Namespaces.
	 *
	 * @var array
	 */

	protected $namespaces = [];

	/**
	 * Sets the defaut path.
	 */

	public function setPath($path)
	{
		$this->path = $path;
	}

	/**
	 * Sets the extension.
	 *
	 * @access  public
	 * @param   string  $extension  Extension
	 */

	public function setExtension($extension)
	{
		$this->extension = $extension;
	}

	/**
	 * Registers a namespace.
	 *
	 * @access  public
	 * @param   string  $namespace  Namespace name
	 * @param   string  $path       Namespace path
	 */

	public function registerNamespace($namespace, $path)
	{
		$this->namespaces[$namespace] = $path;
	}

	/**
	 * Returns the path to the file.
	 *
	 * @access  protected
	 * @param   string     $file       File name
	 * @param   string     $extension  File extension
	 * @param   string     $suffix     Path suffix
	 * @return  string
	 */

	protected function getFilePath($file, $extension = null, $suffix = null)
	{
		if(strpos($file, '::') === false)
		{
			// No namespace so we'll just use the default path

			$path = $this->path;
		}
		else
		{
			// The file is namespaced so we'll use the namespace path

			list($namespace, $file) = explode('::', $file, 2);

			if(!isset($this->namespaces[$namespace]))
			{
				throw new RuntimeException(vsprintf("%s(): The [ %s ] namespace does not exist.", [__METHOD__, $namespace]));
			}

			$path = $this->namespaces[$namespace];
		}

		// Append suffix to path if needed

		if($suffix !== null)
		{
			$path .= DIRECTORY_SEPARATOR . $suffix;
		}

		// Return full path to file

		return $path . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $file) . ($extension ?: $this->extension);
	}

	/**
	 * Returns an array of cascading file paths.
	 *
	 * @access  protected
	 * @param   string     $file       File name
	 * @param   string     $extension  File extension
	 * @param   string     $suffix     Path suffix
	 * @return  array
	 */

	protected function getCascadingFilePaths($file, $extension = null, $suffix = null)
	{
		$paths = [];

		if(strpos($file, '::') === false)
		{
			// No namespace so we'll just have add a single file

			$paths[] = $this->getFilePath($file, $extension, $suffix);
		}
		else
		{
			// Add the namespaced file first

			$paths[] = $this->getFilePath($file, $extension, $suffix);

			// Prepend the cascading file

			list($package, $file) = explode('::', $file);

			$suffix = 'packages' . DIRECTORY_SEPARATOR . $package . (($suffix !== null) ? DIRECTORY_SEPARATOR . $suffix : '');

			array_unshift($paths, $this->getFilePath($file, $extension, $suffix));
		}

		return $paths;
	}
}