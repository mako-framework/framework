<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\i18n\loaders;

use mako\common\traits\NamespacedFileLoaderTrait;
use mako\file\FileSystem;
use mako\i18n\loaders\exceptions\LoaderException;

use function vsprintf;

/**
 * Language loader.
 */
class Loader implements LoaderInterface
{
	use NamespacedFileLoaderTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected FileSystem $fileSystem,
		string $path
	)
	{
		$this->path = $path;
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadInflection(string $language): ?array
	{
		$path = $this->getFilePath('inflection', suffix: $language);

		if($this->fileSystem->has($path))
		{
			return $this->fileSystem->include($path);
		}

		return null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function loadStrings(string $language, string $file): array
	{
		$strings = false;

		foreach($this->getCascadingFilePaths($file, suffix: "{$language}/strings") as $file)
		{
			if($this->fileSystem->has($file))
			{
				$strings = $this->fileSystem->include($file);

				break;
			}
		}

		if($strings === false)
		{
			throw new LoaderException(vsprintf('The [ %s ] language pack does not have a [ %s ] file.', [$language, $file]));
		}

		return $strings;
	}
}
