<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\config\loaders;

use mako\common\traits\NamespacedFileLoaderTrait;
use mako\file\FileSystem;
use RuntimeException;

/**
 *  Loader.
 *
 * @author Frederic G. Østby
 */
class Loader implements LoaderInterface
{
	use NamespacedFileLoaderTrait;

	/**
	 * File system instance.
	 *
	 * @var \mako\file\FileSystem
	 */
	protected $fileSystem;

	/**
	 * Constructor.
	 *
	 * @param \mako\file\FileSystem $fileSystem File system instance
	 * @param string                $path       Default path
	 */
	public function __construct(FileSystem $fileSystem, string $path)
	{
		$this->fileSystem = $fileSystem;

		$this->path = $path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load(string $file, string $environment = null): array
	{
		$loaded = 0;

		$config = [];

		// Load configuration

		foreach(array_reverse($this->getCascadingFilePaths($file)) as $path)
		{
			if($this->fileSystem->has($path))
			{
				$loaded++;

				$config = array_replace_recursive($config, $this->fileSystem->include($path));
			}
		}

		if($loaded === 0)
		{
			throw new RuntimeException(vsprintf('The [ %s ] config file does not exist.', [$file]));
		}

		// Merge environment specific configuration

		if($environment !== null)
		{
			$namespace = strpos($file, '::');

			$namespaced = ($namespace === false) ? $environment . '.' . $file : substr_replace($file, $environment . '.', $namespace + 2, 0);

			foreach(array_reverse($this->getCascadingFilePaths($namespaced)) as $path)
			{
				if($this->fileSystem->has($path))
				{
					$config = array_replace_recursive($config, $this->fileSystem->include($path));
				}
			}
		}

		return $config;
	}
}
