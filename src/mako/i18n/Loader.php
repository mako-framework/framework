<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\i18n;

use \RuntimeException;

use \mako\file\FileSystem;

/**
 * Language loader.
 *
 * @author  Frederic G. Østby
 */

class Loader
{
	use \mako\common\NamespacedFileLoaderTrait;

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
	 * Returns language file patterns.
	 * 
	 * @access  public
	 * @param   string  $language  Name of the language pack
	 * @return  array
	 */

	protected function getPatterns($language)
	{
		$patterns = [['prefix' => null, 'path' => $this->getFilePath('*', null, $language . '/strings')]];

		foreach($this->namespaces as $namespace => $path)
		{
			$patterns[] = ['prefix' => $namespace . '::', 'path' => $this->getFilePath($namespace . '::*', null, $language . '/strings')];
		}

		return $patterns;
	}

	/**
	 * Loads and returns language strings.
	 * 
	 * @access  public
	 * @param   string  $language  Name of the language pack
	 * @return  array
	 */

	public function loadStrings($language)
	{
		$strings = [];

		$patterns = $this->getPatterns($language);

		foreach($patterns as $pattern)
		{
			$files = $this->fileSystem->glob($pattern['path'], GLOB_NOSORT);

			foreach($files as $file)
			{
				$strings[$pattern['prefix'] . basename($file, '.php')] = $this->fileSystem->includeFile($file);
			}
		}

		return $strings;
	}
}