<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\i18n;

use \mako\cache\Cache;
use \mako\file\FileSystem;
use \mako\i18n\Language;

/**
 * Internationalization class.
 *
 * @author  Frederic G. Østby
 */

class I18n
{
	/**
	 * File system instance.
	 * 
	 * @var \mako\file\FileSystem
	 */

	protected $fileSystem;

	/**
	 * Application path.
	 * 
	 * @var string
	 */

	protected $applicationPath;

	/**
	 * Current language.
	 *
	 * @var string
	 */

	protected $language;

	/**
	 * Loaded languages.
	 *
	 * @var array
	 */

	protected $languages = [];

	/**
	 * Cache instance.
	 * 
	 * @var \mako\cache\Cache
	 */

	protected $cache;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $fileSystem       File system instance
	 * @param   string  $applicationPath  Application path
	 * @param   string  $language         Name of the language pack
	 */

	public function __construct(FileSystem $fileSystem, $applicationPath, $language)
	{
		$this->fileSystem = $fileSystem;

		$this->applicationPath = $applicationPath;

		$this->language = $language;
	}

	/**
	 * Sets the cache.
	 * 
	 * @access  public
	 * @param   \mako\cache\Cache  $cache  Cache instance
	 */

	public function setCache(Cache $cache)
	{
		$this->cache = $cache;
	}

	/**
	 * Gets the current language
	 * 
	 * @access  public
	 * @param   string  $language  Name of the language pack
	 */

	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Sets the current language
	 *
	 * @access  public
	 * @param   string  $language  Name of the language pack
	 * @return  string
	 */

	public function setLanguage($language = null)
	{
		$this->language = $language;
	}

	/**
	 * Returns the chosen language.
	 * 
	 * @access  protected
	 * @param   string               $language  Name of the language pack
	 * @return  \mako\i18n\Language
	 */

	protected function language($language = null)
	{
		$language = $language ?: $this->language;

		if(!isset($this->languages[$language]))
		{
			$this->languages[$language] = new Language($this->fileSystem, $this->applicationPath, $language, $this->cache);
		}

		return $this->languages[$language];
	}

	/**
	 * Returns TRUE if the string exists and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $key       String to translate
	 * @param   string   $language  (optional) Name of the language you want to translate to
	 * @return  boolean
	 */

	public function has($key, $language = null)
	{
		return $this->language($language)->has($key);
	}

	/**
	 * Returns the chosen string from the current language. 
	 *
	 * @access  public
	 * @param   string  $key       String to translate
	 * @param   array   $vars      (optional) Value or array of values to replace in the translated text
	 * @param   string  $language  (optional) Name of the language you want to translate to
	 * @return  string
	 */

	public function get($key, array $vars = [], $language = null)
	{
		return $this->language($language)->get($key, $vars);
	}

	/**
	 * Returns the plural form of a noun.
	 *
	 * @access  public
	 * @param   string  $word      Noun to pluralize
	 * @param   int     $count     (optional) Number of nouns
	 * @param   string  $language  (optional) Language rules to use for pluralization
	 * @return  string
	 */

	public function pluralize($word, $count = null, $language = null)
	{
		return $this->language($language)->pluralize($word, $count);
	}
}