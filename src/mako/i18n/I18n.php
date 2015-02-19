<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\i18n;

use RuntimeException;

use mako\cache\Cache;
use mako\i18n\Loader;
use mako\utility\Arr;

/**
 * Internationalization class.
 *
 * @author  Frederic G. Østby
 */

class I18n
{
	/**
	 * Language loader.
	 *
	 * @var \mako\i18n\Loader
	 */

	protected $loader;

	/**
	 * Current language.
	 *
	 * @var string
	 */

	protected $language;

	/**
	 * Loaded language strings.
	 *
	 * @var array
	 */

	protected $strings = [];

	/**
	 * Loaded language inflections.
	 *
	 * @var array
	 */

	protected $inflections = [];

	/**
	 * Cache instance.
	 *
	 * @var \mako\cache\Cache
	 */

	protected $cache;

	/**
	 * Should we rebuild the cache?
	 *
	 * @var boolean
	 */

	protected $rebuildCache = false;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\i18n\Loader  $loader    Loader instance
	 * @param   string             $language  Default language pack name
	 * @param   \mako\cache\Cache  $cache     Cache instance
	 */

	public function __construct(Loader $loader, $language, Cache $cache = null)
	{
		$this->loader = $loader;

		$this->language = $language;

		$this->cache = $cache;
	}

	/**
	 * Destructor.
	 *
	 * @access  public
	 */

	public function __destruct()
	{
		if($this->cache !== null && $this->rebuildCache)
		{
			foreach($this->strings as $language => $strings)
			{
				$this->cache->put('mako.i18n.' . $language, $strings, 3600);
			}
		}
	}

	/**
	 * Returns the string loader.
	 *
	 * @access  public
	 * @return  \mako\i18n\Loader
	 */

	public function getLoader()
	{
		return $this->loader;
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
	 * @return  string
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
	 * Loads inflection closure and rules.
	 *
	 * @access  protected
	 * @param   string     $language  Name of the language pack
	 */

	protected function loadInflection($language)
	{
		$this->inflections[$language] = $this->loader->loadInflection($language);
	}

	/**
	 * Returns the plural form of a noun.
	 *
	 * @access  public
	 * @param   string  $word      Noun to pluralize
	 * @param   int     $count     Number of nouns
	 * @param   string  $language  Language rules to use for pluralization
	 * @return  string
	 */

	public function pluralize($word, $count = null, $language = null)
	{
		$language = $language ?: $this->language;

		if(!isset($this->inflections[$language]))
		{
			$this->loadInflection($language);
		}

		if(empty($this->inflections[$language]))
		{
			throw new RuntimeException(vsprintf("%s(): The [ %s ] language pack does not include any inflection rules.", [__METHOD__, $language]));
		}

		$pluralizer = $this->inflections[$language]['pluralize'];

		return $pluralizer($word, (int) $count, $this->inflections[$language]['rules']);
	}

	/**
	 * Loads language strings from cache.
	 *
	 * @access  protected
	 * @param   string     $language  Name of the language pack
	 * @param   string     $file      File from which we are loading the strings
	 * @return  boolean
	 */

	protected function loadFromCache($language, $file)
	{
		$this->strings[$language] = $this->cache->get('mako.i18n.' . $language);

		return $this->strings[$language] !== false && isset($this->strings[$language][$file]);
	}

	/**
	 * Loads all strings for the language.
	 *
	 * @access  protected
	 * @param   string     $language  Name of the language pack
	 * @param   file       $file      File from which we are loading the strings
	 */

	protected function loadStrings($language, $file)
	{
		if($this->cache !== null)
		{
			if($this->loadFromCache($language, $file))
			{
				return;
			}

			$this->rebuildCache = true;
		}

		$this->strings[$language][$file] = $this->loader->loadStrings($language, $file);
	}

	/**
	 * Parses the language key.
	 *
	 * @access  protected
	 * @param   string    $key  Language key
	 * @return  array
	 */

	protected function parseKey($key)
	{
		return explode('.', $key, 2);
	}

	/**
	 * Returns the language string.
	 *
	 * @access  protected
	 * @param   string     $key       Language key
	 * @param   string     $language  Name of the language pack
	 * @return  string
	 */

	protected function getString($key, $language)
	{
		$language = $language ?: $this->language;

		list($file, $string) = $this->parseKey($key);

		if(!isset($this->strings[$language][$file]))
		{
			$this->loadStrings($language, $file);
		}

		return Arr::get($this->strings[$language][$file], $string, $key);
	}

	/**
	 * Returns TRUE if the string exists and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $key       String to translate
	 * @param   string   $language  Name of the language pack
	 * @return  boolean
	 */

	public function has($key, $language = null)
	{
		$language = $language ?: $this->language;

		list($file, $string) = $this->parseKey($key);

		if(!isset($this->strings[$language][$file]))
		{
			$this->loadStrings($language, $file);
		}

		return Arr::has($this->strings[$language][$file], $string);
	}

	/**
	 * Pluralize words between pluralization tags.
	 *
	 * @access  protected
	 * @param   string     $string  String to parse
	 * @return  string
	 */

	protected function parsePluralizationTags($string)
	{
		if(stripos($string, '</pluralize>') !== false)
		{
			$string = preg_replace_callback('/\<pluralize:([0-9]+)\>(.*)\<\/pluralize\>/iu', function($matches)
			{
				return $this->pluralize($matches[2], (int) $matches[1]);
			}, $string);
		}

		return $string;
	}

	/**
	 * Returns the chosen string from the current language.
	 *
	 * @access  public
	 * @param   string  $key       String to translate
	 * @param   array   $vars      Array of values to replace in the translated text
	 * @param   string  $language  Name of the language pack
	 * @return  string
	 */

	public function get($key, array $vars = [], $language = null)
	{
		$string = $this->getString($key, $language);

		if(!empty($vars))
		{
			$string = $this->parsePluralizationTags(vsprintf($string, $vars));
		}

		return $string;
	}
}