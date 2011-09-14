<?php

namespace mako
{
	use \Mako;
	use \mako\Cache;
	use \Exception;
	
	/**
	* Internationalization class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
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
					throw new Exception(__CLASS__ . ": Invalid i18n language pack name.");
				}

				if(is_dir(MAKO_APPLICATION.'/i18n/' . $language))
				{
					static::$language = $language;
				}
				else
				{
					throw new Exception(__CLASS__ . ": The '{$language}' i18n language pack does not exist.");
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

				$files = scandir(MAKO_APPLICATION . '/i18n/' . static::$language);

				foreach($files as $file)
				{
					if($file[0] !== '.')
					{
						$lang = include(MAKO_APPLICATION . '/i18n/' .static::$language . '/' . $file);

						static::$translationTable = array_merge(static::$translationTable, $lang);

						unset($lang);
					}
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