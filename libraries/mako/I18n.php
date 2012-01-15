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

		protected static $language = 'en_GB';

		/**
		* Array holding the translation tables.
		*/

		protected static $translationTable = array();

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
		* Set language to use. It will return the name of the current language.
		*
		* @access  public
		* @param   string  (optional) Name of the language pack
		* @return  string
		*/

		public static function language($language = null)
		{
			if($language !== null)
			{
				// Validate language pack name to avoid potential directory traversal exploits
				// if language name comes from untrusted input such as cookies.

				if(preg_match('/^[a-z]{2}[_][A-Z]{2}$/', $language) === 0)
				{
					throw new RuntimeException(vsprintf("%s(): Invalid i18n language name.", array(__METHOD__)));
				}

				if(is_dir(MAKO_APPLICATION.'/i18n/' . $language))
				{
					static::$language = $language;
				}
				else
				{
					throw new RuntimeException(vsprintf("%s(): The '%s' language pack does not exist.", array(__METHOD__, $language)));
				}

				static::$translationTable = array(); // Reset the translation table
			}

			return static::$language;
		}

		/**
		* Returns a translated string of the current language. 
		* If no translation exists then the submitted string will be returned.
		*
		* @access  public
		* @param   string  Text to translate
		* @param   arrray  (optional) Value or array of values to replace in the translated text
		* @return  string
		*/

		public static function getText($string, array $vars = null)
		{	
			if(empty(static::$translationTable))
			{			
				static::load();
			}

			$string = isset(static::$translationTable[$string]) ? static::$translationTable[$string] : $string;

			return ($vars === null) ? $string : vsprintf($string, $vars);
		}

		/**
		* Loads the translation tables for the current language.
		*
		* @access  protected
		*/

		protected static function load()
		{	
			static::$translationTable = false;

			if(MAKO_INTERNAL_CACHE === true)
			{
				static::$translationTable = Cache::instance()->read(MAKO_APPLICATION_ID . '_lang_' . static::$language);
			}

			if(static::$translationTable === false)
			{
				static::$translationTable = array();

				// Fetch strings from bundles

				$files = glob(MAKO_BUNDLES . '/*/i18n/' . static::$language . '/*.php', GLOB_NOSORT);

				foreach($files as $file)
				{
					static::$translationTable = array_merge(static::$translationTable, include($file));
				}

				// Fetch strings from application

				$files = glob(MAKO_APPLICATION . '/i18n/' . static::$language . '/*.php', GLOB_NOSORT);

				foreach($files as $file)
				{
					static::$translationTable = array_merge(static::$translationTable, include($file));
				}

				if(MAKO_INTERNAL_CACHE === true)
				{
					Cache::instance()->write(MAKO_APPLICATION_ID . '_lang_' .static::$language, static::$translationTable, 3600);
				}
			}
		}
	}
}

/** -------------------- End of file --------------------**/