<?php

namespace mako;

use \mako\Config;
use \mako\Cache;
use \RuntimeException;

/**
 * Internationalization class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class I18n
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Current language.
	 *
	 * @var string
	 */

	protected $language;

	/**
	 * Enable caching?
	 * 
	 * @var boolean
	 */

	protected $cache;

	/**
	 * Array holding the language strings.
	 *
	 * @var array
	 */

	protected $strings = array();

	/**
	 * Array holding inflection rules.
	 *
	 * @var array
	 */

	protected $inflection = array();

	/**
	 * Singleton instance.
	 * 
	 * @var mako\I18n
	 */

	protected static $instance = null;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Protected constructor since this is a singleton.
	 *
	 * @access  protected
	 */

	protected function __construct()
	{
		$config = Config::get('application');

		$this->language = $config['default_language'];
		$this->cache    = $config['language_cache'];
	}

	/**
	 * Returns singleton instance of the I18n class.
	 * 
	 * @access  public
	 * @return  mako\I18n
	 */

	public static function instance()
	{
		if(static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Checks if a language pack exists and throws an exception if it doesn't.
	 *
	 * @access  protected
	 * @param   string     $language  Name of the language pack
	 */

	protected function languageExists($language)
	{
		if(!is_dir(MAKO_APPLICATION_PATH . '/i18n/' . $language))
		{
			throw new RuntimeException(vsprintf("%s(): The '%s' language pack does not exist.", array(__METHOD__, $language)));
		}
	}

	/**
	 * Set and/or get the default language.
	 *
	 * @access  public
	 * @param   string  $language  (optional) Name of the language pack
	 * @return  string
	 */

	protected function language($language = null)
	{
		if($language !== null)
		{
			$this->languageExists($language);

			$this->language = $language;
		}

		return $this->language;
	}

	/**
	 * Returns TRUE if the string exists and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $string    String to translate
	 * @param   string   $language  (optional) Name of the language you want to translate to
	 * @return  boolean
	 */

	protected function has($string, $language = null)
	{
		$language = $language ?: $this->language;

		if(empty($this->strings[$language]))
		{			
			$this->loadStrings($language);
		}

		return isset($this->strings[$language][$string]);
	}

	/**
	 * Returns a translated string of the current language. 
	 *
	 * @access  public
	 * @param   string  $string    String to translate
	 * @param   array   $vars      (optional) Value or array of values to replace in the translated text
	 * @param   string  $language  (optional) Name of the language you want to translate to
	 * @return  string
	 */

	protected function translate($string, array $vars = array(), $language = null)
	{
		$language = $language ?: $this->language;

		if(empty($this->strings[$language]))
		{			
			$this->loadStrings($language);
		}

		$string = $this->has($string, $language) ? $this->strings[$language][$string] : $string;

		return (empty($vars)) ? $string : vsprintf($string, $vars);
	}

	/**
	 * Returns the plural form of a noun.
	 *
	 * @access  public
	 * @param   string  $word      Noun to pluralize
	 * @param   int     $count     (optional) Number of "<noun>s"
	 * @param   string  $language  (optional) Language rules to use for pluralization
	 * @return  string
	 */

	protected function plural($word, $count = null, $language = null)
	{
		$language = $language ?: $this->language;

		if(empty($this->inflection[$language]))
		{			
			$this->loadInflection($language);
		}

		return call_user_func($this->inflection[$language]['pluralize'], $word, $count, $this->inflection[$language]['rules']);
	}

	/**
	 * Loads the inflection rules for the requested language.
	 *
	 * @access  protected
	 * @param   string     $language  Name of the language pack
	 */

	protected function loadInflection($language)
	{
		$this->languageExists($language);

		if(file_exists(MAKO_APPLICATION_PATH . '/i18n/' . $language . '/inflection.php'))
		{
			$this->inflection[$language] = include(MAKO_APPLICATION_PATH . '/i18n/' . $language . '/inflection.php');
		}
		else
		{
			throw new RuntimeException(vsprintf("%s:(): The '%s' language pack does not contain any inflection rules.", array(__METHOD__, $language)));
		}
	}

	/**
	 * Loads the translation strings for the requested language.
	 *
	 * @access  protected
	 * @param   string     $language  Name of the language pack
	 */

	protected function loadStrings($language)
	{
		$this->languageExists($language);

		$this->strings[$language] = false;

		if($this->cache)
		{
			$this->strings[$language] = Cache::instance()->read(MAKO_APPLICATION_ID . '_lang_' . $language);
		}

		if($this->strings[$language] === false)
		{
			$this->strings[$language] = array();

			$locations = array
			(
				MAKO_PACKAGES_PATH . '/*/i18n/' . $language . '/strings/*.php',
				MAKO_APPLICATION_PATH . '/i18n/' . $language . '/strings/*.php',
			);

			foreach($locations as $location)
			{
				$files = glob($location, GLOB_NOSORT);

				foreach($files as $file)
				{
					$this->strings[$language] = array_merge($this->strings[$language], include($file));
				}
			}

			if($this->cache)
			{
				Cache::instance()->write(MAKO_APPLICATION_ID . '_lang_' . $language, $this->strings[$language], 3600);
			}
		}
	}

	/**
	 * Static interface to the I18n singleton instance.
	 * 
	 * @access  public
	 * @param   string  $name       Method name
	 * @param   array   $arguments  Method arguments
	 */

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array(array(static::instance(), $name), $arguments);
	}
}

/** -------------------- End of file --------------------**/