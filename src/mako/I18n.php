<?php

namespace mako;

use \mako\Config;
use \mako\i18n\Language;
use \RuntimeException;

/**
 * Internationalization class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
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

	protected static $language;

	/**
	 * Loaded languages.
	 *
	 * @var array
	 */

	protected static $languages;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Set and/or get the current language.
	 *
	 * @access  public
	 * @param   string  $language  (optional) Name of the language pack
	 * @return  string
	 */

	public static function language($language = null)
	{
		if($language !== null)
		{
			static::$language = $language;
		}

		if(empty(static::$language))
		{
			static::$language = Config::get('application.default_language');
		}

		return static::$language;
	}

	/**
	 * Returns the instance of the chosen language.
	 * 
	 * @access  public
	 * @param   string               $language  Name of the language pack
	 * @return  \mako\i18n\Language
	 */

	protected static function lang($language)
	{
		$language = $language ?: static::language();

		if(empty(static::$languages[$language]))
		{
			if(!is_dir(MAKO_APPLICATION_PATH . '/i18n/' . $language))
			{
				throw new RuntimeException(vsprintf("%s(): The '%s' language pack does not exist.", array(__METHOD__, $language)));
			}

			static::$languages[$language] = new Language($language, Config::get('application.language_cache'));
		}

		return static::$languages[$language];
	}

	/**
	 * Returns TRUE if the string exists and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $string    String to translate
	 * @param   string   $language  (optional) Name of the language you want to translate to
	 * @return  boolean
	 */

	public static function has($string, $language = null)
	{
		return static::lang($language)->has($string);
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

	public static function translate($string, array $vars = array(), $language = null)
	{
		return static::lang($language)->translate($string, $vars);
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

	public static function pluralize($word, $count = null, $language = null)
	{
		return static::lang($language)->pluralize($word, $count);
	}
}

/** -------------------- End of file --------------------**/