<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\config\loaders;

use mako\common\traits\NamespacedFileLoaderTrait;
use mako\config\loaders\exceptions\LoaderException;
use mako\file\FileSystem;

use function array_merge;
use function strpos;
use function substr_replace;
use function vsprintf;

/**
 *  Loader.
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
	 * {@inheritDoc}
	 */
	public function load(string $file, ?string $environment = null): array
	{
		$paths = $this->getCascadingFilePaths($file);

		// If we have an environment then we must prepend the environment specific config paths to the paths array

		if($environment !== null)
		{
			$namespace = strpos($file, '::');

			$environmentFile = ($namespace === false) ? "{$environment}.{$file}" : substr_replace($file, "{$environment}.", $namespace + 2, 0);

			$paths = array_merge($this->getCascadingFilePaths($environmentFile), $paths);
		}

		// Include the first existing file or throw an exception if we don't find any config files

		foreach($paths as $path)
		{
			if($this->fileSystem->has($path))
			{
				return $this->fileSystem->include($path);
			}
		}

		throw new LoaderException(vsprintf('The [ %s ] config file does not exist.', [$file]));
	}
}
