<?php

namespace mako\i18n;

use \mako\Cache;
use \RuntimeException;

/**
 * Language container.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Language
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Language name.
	 * 
	 * @var string
	 */

	protected $language;

	/**
	 * Array holding the language strings.
	 *
	 * @var array
	 */

	protected $strings = false;

	/**
	 * Array holding inflection rules.
	 *
	 * @var array
	 */

	protected $inflection = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string   $language  Name of the language pack
	 * @param   boolean  $cache     Enable language cache?
	 */

	public function __construct($language, $cache)
	{
		$this->language = $language;

		if($cache)
		{
			$this->strings = Cache::instance()->read(MAKO_APPLICATION_ID . '_lang_' . $this->language);
		}

		if($this->strings === false)
		{
			$this->strings = array();

			$locations = array
			(
				MAKO_PACKAGES_PATH . '/*/i18n/' . $this->language . '/strings/*.php',
				MAKO_APPLICATION_PATH . '/i18n/' . $this->language . '/strings/*.php',
			);

			foreach($locations as $location)
			{
				$files = glob($location, GLOB_NOSORT);

				if(is_array($files))
				{
					foreach($files as $file)
					{
						$this->strings = array_merge($this->strings, include($file));
					}
				}
			}

			if($cache)
			{
				Cache::instance()->write(MAKO_APPLICATION_ID . '_lang_' . $language, $this->strings, 3600);
			}
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Loads the inflection rules for the requested language.
	 *
	 * @access  protected
	 * @param   string     $language  Name of the language pack
	 */

	protected function loadInflection()
	{
		if(file_exists(MAKO_APPLICATION_PATH . '/i18n/' . $this->language . '/inflection.php'))
		{
			$this->inflection = include(MAKO_APPLICATION_PATH . '/i18n/' . $this->language . '/inflection.php');
		}
		else
		{
			throw new RuntimeException(vsprintf("%s:(): The '%s' language pack does not contain any inflection rules.", array(__METHOD__, $this->language)));
		}
	}

	/**
	 * Returns TRUE if the string exists and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $string  String to translate
	 * @return  boolean
	 */

	public function has($string)
	{
		return isset($this->strings[$string]);
	}

	/**
	 * Returns a translated string of the current language. 
	 *
	 * @access  public
	 * @param   string  $string  String to translate
	 * @param   array   $vars    (optional) Value or array of values to replace in the translated text
	 * @return  string
	 */

	public function translate($string, array $vars = array())
	{
		$string = $this->has($string) ? $this->strings[$string] : $string;

		if(!empty($vars))
		{
			$string = vsprintf($string, $vars);

			if(stripos($string, '</pluralize>') !== false)
			{
				$that = $this;
				
				$string = preg_replace_callback('/\<pluralize:([0-9]+)\>(.*)\<\/pluralize\>/iu', function($matches) use ($that)
				{
					return $that->pluralize($matches[2], (int) $matches[1]);
				}, $string);
			}
		}

		return $string;
	}

	/**
	 * Returns the plural form of a noun.
	 *
	 * @access  public
	 * @param   string  $word   Noun to pluralize
	 * @param   int     $count  (optional) Number of "<noun>s"
	 * @return  string
	 */

	public function pluralize($word, $count = null)
	{
		if(empty($this->inflection))
		{			
			$this->loadInflection();
		}

		$pluralizer = $this->inflection['pluralize'];

		return $pluralizer($word, $count, $this->inflection['rules']);
	}
}

/** -------------------- End of file -------------------- **/