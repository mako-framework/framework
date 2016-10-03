<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\config\loaders;

use RuntimeException;

use mako\common\NamespacedFileLoaderTrait;
use mako\file\FileSystem;

/**
 * Array loader.
 *
 * @author  Frederic G. Østby
 */
class ArrayLoader implements LoaderInterface
{
	use NamespacedFileLoaderTrait;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\file\FileSystem $fileSystem   File system instance
	 * @param   string                $path         Default path
	 */
	public function __construct(FileSystem $fileSystem, string $path)
	{
		$this->fileSystem = $fileSystem;

		$this->path = $path;
	}

	/**
	 * Loads the configuration file.
	 *
	 * @access  protected
	 * @param   string       $file         File name
	 * @param   null|string  $environment  Environment
	 * @return  array
	 */
	public function load(string $file, string $environment = null): array
	{
		// Load configuration

		foreach($this->getCascadingFilePaths($file) as $path)
		{
			if($this->fileSystem->has($path))
			{
				$config = $this->fileSystem->include($path);

				break;
			}
		}

		if(!isset($config))
		{
			throw new RuntimeException(vsprintf("%s(): The [ %s ] config file does not exist.", [__METHOD__, $file]));
		}

		// Merge environment specific configuration

		if($environment !== null)
		{
			$namespace = strpos($file, '::');

			$namespaced = ($namespace === false) ? $environment . '.' . $file : substr_replace($file, $environment . '.', $namespace + 2, 0);

			foreach($this->getCascadingFilePaths($namespaced) as $path)
			{
				if($this->fileSystem->has($path))
				{
					$config = array_replace_recursive($config, $this->fileSystem->include($path));

					break;
				}
			}
		}

		return $config;
	}
}