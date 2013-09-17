<?php

namespace mako\i18n;

use \mako\Arr;
use \mako\Cache;
use \mako\Config;
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
	 * Cache strings?
	 *
	 * @var boolean
	 */

	protected $useCache;

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

	protected $strings = array();

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
	 */

	public function __construct($language)
	{
		$this->language = $language;

		$this->useCache = Config::get('application.language_cache');
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Loads the inflection rules for the requested language.
	 *
	 * @access  protected
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

	/**
	 * Loads all strings.
	 * 
	 * @access  protected
	 */

	protected function loadStrings()
	{
		// Load strings from cache if language cache is enabled

		if($this->useCache)
		{
			$this->strings = Cache::instance()->read(MAKO_APPLICATION_ID . '_lang_' . $this->language);
		}

		if($this->strings === false || empty($this->strings))
		{
			$this->strings = array('mako:packages' => array());
			
			// Load language files from the application

			$files = glob(MAKO_APPLICATION_PATH . '/i18n/' . $this->language . '/strings/*.php', GLOB_NOSORT);

			if(is_array($files))
			{
				foreach($files as $file)
				{
					$this->strings[basename($file, '.php')] = include($file);
				}
			}

			// Load language files from installed packages

			$files = glob(MAKO_PACKAGES_PATH . '/*/i18n/' . $this->language . '/strings/*.php', GLOB_NOSORT);

			if(is_array($files))
			{
				foreach($files as $file)
				{
					preg_match('/(.*)\/(.*)\/i18n\/' . $this->language . '\/strings\/(.*).php/', $file, $matches);

					$this->strings['mako:packages'][$matches[2]][$matches[3]] = include($file);
				}
			}

			// Write strings to cache if language cache is enabled

			if($this->useCache)
			{
				Cache::instance()->write(MAKO_APPLICATION_ID . '_lang_' . $this->language, $this->strings, 3600);
			}
		}
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
		if(empty($this->strings))
		{
			$this->loadStrings();
		}
		
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

	public function get($key, array $vars = array())
	{
		if(empty($this->strings))
		{
			$this->loadStrings();
		}

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
				$that = $this;
				
				$string = preg_replace_callback('/\<pluralize:([0-9]+)\>(.*)\<\/pluralize\>/iu', function($matches) use ($that)
				{
					return $that->pluralize($matches[2], (int) $matches[1]);
				}, $string);
			}
		}

		return $string;
	}
}

/** -------------------- End of file -------------------- **/