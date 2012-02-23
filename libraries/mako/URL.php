<?php

namespace mako;

use \mako\Config;

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
	* @param   boolean  (optional) Include 'index.php' if clean URLs are disabled?
	* @return  string
	*/

	public static function base($index = false)
	{
		static $base  = false;
		static $clean = false;

		if($base === false)
		{
			$base  = Config::get('mako.base_url');
			$clean = Config::get('mako.clean_urls');

			// Try to autodetect base url if its not configured

			if($base === '' && isset($_SERVER['HTTP_HOST']))
			{
				$script = $_SERVER['SCRIPT_NAME'];
				
				$base = rtrim($_SERVER['HTTP_HOST'] . str_replace(basename($script), '', $script), '/');
			}
		}

		return ($index && !$clean) ? $base . '/index.php' : $base;
	}

	/**
	* Returns a mako framework url.
	*
	* @access  public
	* @param   string   URL segments
	* @param   array    (optional) Associative array used to build URL-encoded query string
	* @param   string   (optional) Argument separator
	* @return  string
	*/

	public static function to($route = '', array $params = array(), $separator = '&amp;')
	{
		$url = static::base(true) . '/' . $route;
		
		if(!empty($params))
		{
			$url .= '?' . http_build_query($params, '', $separator);
		}
		
		return $url;
	}
}

/** -------------------- End of file --------------------**/