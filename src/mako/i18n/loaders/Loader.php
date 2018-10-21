<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\i18n\loaders;

use mako\common\traits\NamespacedFileLoaderTrait;
use mako\file\FileSystem;

use function vsprintf;

/**
 * Language loader.
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
	public function loadInflection(string $language)
	{
		$path = $this->getFilePath('inflection', null, $language);

		if($this->fileSystem->has($path))
		{
			return $this->fileSystem->include($path);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadStrings(string $language, string $file): array
	{
		$strings = false;

		foreach($this->getCascadingFilePaths($file, null, $language . '/strings') as $file)
		{
			if($this->fileSystem->has($file))
			{
				$strings = $this->fileSystem->include($file);

				break;
			}
		}

		if($strings === false)
		{
			throw new LoaderException(vsprintf('The [ %s ] language pack does not have a [ %s ] file.', [$language, $file]));
		}

		return $strings;
	}
}
