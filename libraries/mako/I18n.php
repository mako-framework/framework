<?php

namespace mako
{
	use \mako\Mako;
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
		// Class variables
		//---------------------------------------------

		/**
		* Current language.
		*/

		protected static $language = 'en_US';

		/**
		* Array holding the language strings.
		*/

		protected static $strings = array();

		/**
		* Array holding inflection rules.
		*/

		protected static $inflection = array();

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Protected constructor since this is a static class.
		*
		* @access  protected
		*/

		protected function __construct()
		{
			// Nothing here
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Checks if a language pack exists and throws an exception if it doesn't.
		*
		* @access  protected
		* @param   string     Name of the language pack
		*/

		protected static function languageExists($language)
		{
			if(!is_dir(MAKO_APPLICATION.'/i18n/' . $language))
			{
				throw new RuntimeException(vsprintf("%s(): The '%s' language pack does not exist.", array(__METHOD__, $language)));
			}
		}

		/**
		* Set and/or get the default language.
		*
		* @access  public
		* @param   string  (optional) Name of the language pack
		* @return  string
		*/

		public static function language($language = null)
		{
			if($language !== null)
			{
				static::languageExists($language);

				static::$language = $language;
			}

			return static::$language;
		}

		/**
		* Returns a translated string of the current language. 
		* If no translation exists then the submitted string will be returned.
		*
		* @access  public
		* @param   string  Text to translate
		* @param   array   (optional) Value or array of values to replace in the translated text
		* @param   string  (optional) Name of the language you want to translate to
		* @return  string
		*/

		public static function getText($string, array $vars = null, $language = null)
		{
			$language = $language === null ? static::$language : $language;

			if(empty(static::$strings[$language]))
			{			
				static::loadStrings($language);
			}

			$string = isset(static::$strings[$language][$string]) ? static::$strings[$language][$string] : $string;

			return ($vars === null) ? $string : vsprintf($string, $vars);
		}

		/**
		* Returns the plural form of a noun.
		*
		* @access  public
		* @param   string  Noun to pluralize
		* @param   int     (optional) Number of "<noun>s"
		* @param   string  (optional) Language rules to use for pluralization
		* @return  string
		*/

		public static function plural($word, $count = null, $language = null)
		{
			$language = $language === null ? static::$language : $language;

			if(empty(static::$inflection[$language]))
			{			
				static::loadInflection($language);
			}

			return call_user_func(static::$inflection[$language]['pluralize'], $word, $count, static::$inflection[$language]['rules']);
		}

		/**
		* Loads the inflection rules for the requested language.
		*
		* @access  protected
		* @param   string     Name of the language pack
		*/

		protected static function loadInflection($language)
		{
			static::languageExists($language);

			if(file_exists(MAKO_APPLICATION . '/i18n/' . $language . '/inflection.php'))
			{
				static::$inflection[$language] = include(MAKO_APPLICATION . '/i18n/' . $language . '/inflection.php');
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
		* @param   string     Name of the language pack
		*/

		protected static function loadStrings($language)
		{
			static::languageExists($language);

			static::$strings[$language] = false;

			if(MAKO_INTERNAL_CACHE === true)
			{
				static::$strings[$language] = Cache::instance()->read(MAKO_APPLICATION_ID . '_lang_' . $language);
			}

			if(static::$strings[$language] === false)
			{
				static::$strings[$language] = array();

				// Fetch strings from bundles

				$files = glob(MAKO_BUNDLES . '/*/i18n/' . $language . '/strings/*.php', GLOB_NOSORT);

				foreach($files as $file)
				{
					static::$strings[$language] = array_merge(static::$strings[$language], include($file));
				}

				// Fetch strings from application

				$files = glob(MAKO_APPLICATION . '/i18n/' . $language . '/strings/*.php', GLOB_NOSORT);

				foreach($files as $file)
				{
					static::$strings[$language] = array_merge(static::$strings[$language], include($file));
				}

				if(MAKO_INTERNAL_CACHE === true)
				{
					Cache::instance()->write(MAKO_APPLICATION_ID . '_lang_' . $language, static::$strings[$language], 3600);
				}
			}
		}
	}
}

/** -------------------- End of file --------------------**/