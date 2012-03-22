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

	// Nothing here

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
			$base  = Config::get('mako.base_url');

			// Try to autodetect base url if its not configured

			if($base === '' && isset($_SERVER['HTTP_HOST']))
			{
				$protocol = Request::isSecure() ? 'https' : 'http';

				$script = $_SERVER['SCRIPT_NAME'];
				
				$base = rtrim($protocol . '://' . $_SERVER['HTTP_HOST'] . str_replace(basename($script), '', $script), '/');
			}

			// Add index.php?

			!Config::get('mako.clean_urls') && $base .= '/index.php';
		}

		return $base;
	}

	/**
	* Returns a mako framework URL.
	*
	* @access  public
	* @param   string   URL segments
	* @param   array    (optional) Associative array used to build URL-encoded query string
	* @param   string   (optional) Argument separator
	* @return  string
	*/

	public static function to($route = '', array $params = array(), $separator = '&amp;')
	{
		$url = static::base() . '/' . $route;
		
		if(!empty($params))
		{
			$url .= '?' . http_build_query($params, '', $separator);
		}
		
		return $url;
	}

	/**
	* Returns the current URL of the main request.
	*
	* @access  public
	* @param   array    (optional) Associative array used to build URL-encoded query string
	* @param   string   (optional) Argument separator
	* @return  string
	*/

	public static function current(array $params = array(), $separator = '&amp;')
	{
		return static::to(Request::route(), $params, $separator);
	}
}

/** -------------------- End of file --------------------**/