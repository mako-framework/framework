<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\config\loaders;

use mako\common\traits\NamespacedFileLoaderTrait;
use mako\config\loaders\exceptions\LoaderException;
use mako\file\FileSystem;
use Override;

use function sprintf;
use function strpos;
use function substr_replace;

/**
 *  Loader.
 */
class Loader implements NamespacedLoaderInterface
{
	use NamespacedFileLoaderTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected FileSystem $fileSystem,
		string $path
	) {
		$this->path = $path;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function load(string $file, ?string $environment = null): array
	{
		$paths = $this->getCascadingFilePaths($file);

		// If we have an environment then we must prepend the environment specific config paths to the paths array

		if ($environment !== null) {
			$namespace = strpos($file, '::');

			$environmentFile = ($namespace === false) ? "{$environment}.{$file}" : substr_replace($file, "{$environment}.", $namespace + 2, 0);

			$paths = [...$this->getCascadingFilePaths($environmentFile), ...$paths];
		}

		// Include the first existing file or throw an exception if we don't find any config files

		foreach ($paths as $path) {
			if ($this->fileSystem->has($path)) {
				return $this->fileSystem->include($path);
			}
		}

		throw new LoaderException(sprintf('The [ %s ] config file does not exist.', $file));
	}
}
