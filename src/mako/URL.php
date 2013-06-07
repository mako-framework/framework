<?php

namespace mako;

use \mako\Config;
use \mako\Request;
use \mako\request\Router;

/**
 * URL helper.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class URL
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Are we using clean URLs?
	 *
	 * @var boolean
	 */

	protected static $clean;

	/**
	 * Request language.
	 * 
	 * @var string
	 */

	protected static $language;

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
	 * Returns the base URL of the application.
	 *
	 * @access  public
	 * @return  string
	 */

	public static function base()
	{
		static $base = false;

		if($base === false)
		{
			$base = Config::get('application.base_url');

			// Try to autodetect base url if its not configured

			if($base === '' && isset($_SERVER['HTTP_HOST']))
			{
				$protocol = Request::isSecure() ? 'https' : 'http';

				$script = $_SERVER['SCRIPT_NAME'];
				
				$base = rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . str_replace(basename($script), '', $script), '/');
			}

			// Are we using clean URLs?

			static::$clean = Config::get('application.clean_urls');

			// Add request language to URL?

			$language = Request::language();

			if(!empty($language))
			{
				static::$language = '/' . $language;
			}
		}

		return $base;
	}

	/**
	 * Returns a mako framework URL.
	 *
	 * @access  public
	 * @param   string   $route      URL segments
	 * @param   array    $params     (optional) Associative array used to build URL-encoded query string
	 * @param   string   $separator  (optional) Argument separator
	 * @param   mixed    $language   (optional) Request language
	 * @return  string
	 */

	public static function to($route = '', array $params = array(), $separator = '&amp;', $language = true)
	{
		// Replace the package prefix with the package base route

		if(strpos($route, '::') !== false)
		{
			$route = Router::packageRoute($route);
		}

		// Build and return url

		$url = static::base() . (static::$clean ? '' : '/index.php') . ($language === true ? static::$language : (!$language ? '' : '/' . $language)) . '/' . $route;
		
		if(!empty($params))
		{
			$url .= '?' . http_build_query($params, '', $separator);
		}
		
		return rtrim($url, '/');
	}

	/**
	 * Returns a mako framework URL.
	 *
	 * @access  public
	 * @param   string   $route      URL segments
	 * @param   mixed    $language   (optional) Request language
	 * @param   array    $params     (optional) Associative array used to build URL-encoded query string
	 * @param   string   $separator  (optional) Argument separator
	 * @return  string
	 */

	public static function toLanguage($route = '', $language = true, array $params = array(), $separator = '&amp;')
	{
		return static::to($route, $params, $separator, $language);
	}

	/**
	 * Returns the current URL of the main request.
	 *
	 * @access  public
	 * @param   array    $params     (optional) Associative array used to build URL-encoded query string
	 * @param   string   $separator  (optional) Argument separator
	 * @param   mixed    $language   (optional) Request language
	 * @return  string
	 */

	public static function current(array $params = array(), $separator = '&amp;', $language = true)
	{
		return static::to(Request::main()->route(), $params, $separator, $language);
	}

	/**
	 * Returns the current URL of the main request.
	 *
	 * @access  public
	 * @param   mixed    $language   (optional) Request language
	 * @param   array    $params     (optional) Associative array used to build URL-encoded query string
	 * @param   string   $separator  (optional) Argument separator
	 * @return  string
	 */

	public static function currentLanguage($language = true, array $params = array(), $separator = '&amp;')
	{
		return static::current($params, $separator, $language);
	}

	/**
	 * Returns TRUE if the pattern matches the current route and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $pattern  Pattern to match
	 * @return  boolean
	 */

	public static function matches($pattern)
	{
		return (bool) preg_match('#' . $pattern . '#', Request::main()->route());
	}
}

/** -------------------- End of file -------------------- **/