<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\i18n;

use \RuntimeException;

use \mako\utility\Arr;

/**
 * Language container.
 *
 * @author  Frederic G. Østby
 */

class Language
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

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
	 * Array holding the language strings.
	 *
	 * @var array
	 */

	protected $strings = [];

	/**
	 * Array holding inflection rules.
	 *
	 * @var array
	 */

	protected $inflection = [];

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $applicationPath  Application path
	 * @param   string  $language         Name of the language pack
	 */

	public function __construct($applicationPath, $language)
	{
		$this->applicationPath = $applicationPath;

		$this->language = $language;

		$this->strings = $this->loadStrings();

		$this->inflection = $this->loadInflection();
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Loads all strings.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function loadStrings()
	{
		$strings = ['mako:packages' => []];
		
		// Load language files from the application

		$files = glob($this->applicationPath . '/i18n/' . $this->language . '/strings/*.php', GLOB_NOSORT);

		if(is_array($files))
		{
			foreach($files as $file)
			{
				$strings[basename($file, '.php')] = include($file);
			}
		}

		// Load language files from installed packages

		$files = glob($this->applicationPath . '/*/i18n/' . $this->language . '/strings/*.php', GLOB_NOSORT);

		if(is_array($files))
		{
			foreach($files as $file)
			{
				preg_match('/(.*)\/(.*)\/i18n\/' . $this->language . '\/strings\/(.*).php/', $file, $matches);

				$strings['mako:packages'][$matches[2]][$matches[3]] = include($file);
			}
		}

		return $strings;
	}

	/**
	 * Loads the inflection rules for the requested language.
	 *
	 * @access  protected
	 * @return  array
	 */

	protected function loadInflection()
	{
		if(file_exists($this->applicationPath . '/i18n/' . $this->language . '/inflection.php'))
		{
			return include($this->applicationPath . '/i18n/' . $this->language . '/inflection.php');
		}
		else
		{
			return [];
		}
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
			throw new RuntimeException(vsprintf("%s:(): The [ %s ] language pack does not contain any inflection rules.", [__METHOD__, $this->language]));
		}

		$pluralizer = $this->inflection['pluralize'];

		return $pluralizer($word, (int) $count, $this->inflection['rules']);
	}

	/**
	 * Returns TRUE if the string exists and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $key  String to look for
	 * @return  boolean
	 */

	public function has($key)
	{
		if(stripos($key, '::'))
		{
			return Arr::has($this->strings['mako:packages'], str_replace('::', '.', $key));
		}
		else
		{
			return Arr::has($this->strings, $key);
		}
	}

	/**
	 * Returns the chosen string. 
	 *
	 * @access  public
	 * @param   string  $key   String to get
	 * @param   array   $vars  (optional) Value or array of values to replace in the translated text
	 * @return  string
	 */

	public function get($key, array $vars = [])
	{
		if(stripos($key, '::'))
		{
			$string = Arr::get($this->strings['mako:packages'], str_replace('::', '.', $key), $key);
		}
		else
		{
			$string = Arr::get($this->strings, $key, $key);
		}

		if(!empty($vars))
		{
			$string = vsprintf($string, $vars);

			if(stripos($string, '</pluralize>') !== false)
			{
				$string = preg_replace_callback('/\<pluralize:([0-9]+)\>(.*)\<\/pluralize\>/iu', function($matches)
				{
					return $this->pluralize($matches[2], (int) $matches[1]);
				}, $string);
			}
		}

		return $string;
	}
}