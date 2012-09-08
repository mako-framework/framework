<?php

namespace mako;

use \mako\Config;
use \mako\Request;

/**
* URL helper.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class URL
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Are we using clean URLs?
	*
	* @var boolean
	*/

	protected static $clean;

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
			$base  = Config::get('application.base_url');

			// Try to autodetect base url if its not configured

			if($base === '' && isset($_SERVER['HTTP_HOST']))
			{
				$protocol = Request::isSecure() ? 'https' : 'http';

				$script = $_SERVER['SCRIPT_NAME'];
				
				$base = rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . str_replace(basename($script), '', $script), '/');
			}

			// Are we using clean URLs?

			static::$clean = Config::get('application.clean_urls');
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
	* @return  string
	*/

	public static function to($route = '', array $params = array(), $separator = '&amp;')
	{
		$url = static::base() . (static::$clean ? '' : '/index.php') . '/' . $route;
		
		if(!empty($params))
		{
			$url .= '?' . http_build_query($params, '', $separator);
		}
		
		return rtrim($url, '/');
	}

	/**
	* Returns the current URL of the main request.
	*
	* @access  public
	* @param   array    $params     (optional) Associative array used to build URL-encoded query string
	* @param   string   $separator  (optional) Argument separator
	* @return  string
	*/

	public static function current(array $params = array(), $separator = '&amp;')
	{
		return static::to(Request::route(), $params, $separator);
	}

	/**
	* Returns TRUE if the pattern matches the current URL and FALSE if not.
	*
	* @access  public
	* @param   string   $pattern  Pattern to match
	* @return  boolean
	*/

	public static function matches($pattern)
	{
		return (boolean) preg_match('#' . $pattern . '#', Request::route());
	}
}

/** -------------------- End of file --------------------**/