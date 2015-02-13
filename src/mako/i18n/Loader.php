<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\i18n;

use RuntimeException;

use mako\common\NamespacedFileLoaderTrait;
use mako\file\FileSystem;

/**
 * Language loader.
 *
 * @author  Frederic G. Østby
 */

class Loader
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
	 * @access  public
	 * @param   \mako\file\FileSystem  $fileSystem  File system instance
	 * @param   string                 $path        Default path
	 */

	public function __construct(FileSystem $fileSystem, $path)
	{
		$this->fileSystem = $fileSystem;

		$this->path = $path;
	}

	/**
	 * Returns inflection rules and closure or NULL if it doesn't exist.
	 *
	 * @access  public
	 * @param   string      $language  Name of the language pack
	 * @return  array|null
	 */

	public function loadInflection($language)
	{
		$path = $this->getFilePath('inflection', null, $language);

		if($this->fileSystem->exists($path))
		{
			return $this->fileSystem->includeFile($path);
		}
	}

	/**
	 * Loads and returns language strings.
	 *
	 * @access  public
	 * @param   string  $language  Name of the language pack
	 * @param   string  $file      File we want to load
	 * @return  array
	 */

	public function loadStrings($language, $file)
	{
		$strings = false;

		foreach($this->getCascadingFilePaths($file, null, $language . '/strings') as $file)
		{
			if($this->fileSystem->exists($file))
			{
				$strings = $this->fileSystem->includeFile($file);

				break;
			}
		}

		if($strings === false)
		{
			throw new RuntimeException(vsprintf("%s(): The [ %s ] language file does not exist in the [ %s ] language pack.", [__METHOD__, $file, $language]));
		}

		return $strings;
	}
}