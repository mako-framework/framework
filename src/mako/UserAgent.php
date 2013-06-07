<?php

namespace mako;

use \mako\Config;

/**
 * Class that helps identifying the device or type of device that made the request.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class UserAgent
{
	//---------------------------------------------
	// Class properties
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
	 * Searches for a string in the user agent string.
	 *
	 * @access  protected
	 * @param   array      $what  Array of strings to look for
	 * @return  boolean
	 */
	
	protected static function find($what)
	{
		if(isset($_SERVER['HTTP_USER_AGENT']))
		{
			foreach($what as $find)
			{
				if(stripos($_SERVER['HTTP_USER_AGENT'], $find) !== false)
				{
					return true;
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Converts the HTTP_ACCEPT headers into an array.
	 *
	 * @access  protected
	 * @param   string     $header  Array key
	 * @param   string     $what    (optional) String to look for
	 * @return  mixed
	 */
	
	protected static function accept($header, $what = null)
	{
		if(isset($_SERVER[$header]))
		{
			$accepts = explode(',', preg_replace('/(;q=[0-9\.]+)/i', '', strtolower(trim($_SERVER[$header]))));
			
			if($what === null && !empty($accepts))
			{
				return $accepts;
			}
			else
			{
				return in_array(strtolower($what), $accepts);
			}
		}
		
		return false;
	}
	
	/**
	 * Returns true if the user agent that made the request is identified as a mobile device.
	 *
	 * @access  public
	 * @return  boolean
	 */
	
	public static function isMobile()
	{
		return static::find(Config::get('user_agents.mobiles'));
	}
	
	/**
	 * Returns true if the user agent that made the request is identified as a robot/crawler.
	 *
	 * @access  public
	 * @return  boolean
	 */
	
	public static function isRobot()
	{
		return static::find(Config::get('user_agents.robots'));
	}
	
	/**
	 * Returns TRUE if the string you're looking for exists in the user agent string and FALSE if not.
	 * Can be used to detect the type of device (UserAgent::is('iphone') or UserAgent::is(array('iphone', 'ipod'))).
	 *
	 * @access  public
	 * @param   mixed    $device  String or array of strings you're looking for
	 * @return  boolean
	 */
	
	public static function is($device)
	{
		return static::find((array) $device);
	}
	
	/**
	 * Returns array of accepted content types or boolean value if you are looking for a content type.
	 *
	 * @access  public
	 * @param   string  $content  (optional) Language to look for.
	 * @return  mixed
	 */
	
	public static function accepts($content = null)
	{
		return static::accept('HTTP_ACCEPT', $content);
	}
	
	/**
	 * Returns array of accepted languages or boolean value if you are looking for a language.
	 *
	 * @access  public
	 * @param   string  $language  (optional) Language to look for.
	 * @return  mixed
	 */
	
	public static function acceptsLanguage($language = null)
	{
		return static::accept('HTTP_ACCEPT_LANGUAGE', $language);
	}
	
	/**
	 * Returns array of accepted character sets or boolean value if you are looking for a character set.
	 *
	 * @access  public
	 * @param   string  $charset  (optional) Character set to look for.
	 * @return  mixed
	 */
	
	public static function acceptsCharset($charset = null)
	{
		return static::accept('HTTP_ACCEPT_CHARSET', $charset);
	}
	
	/**
	 * Returns array of accepted encodings or boolean value if you are looking for an encoding.
	 *
	 * @access  public
	 * @param   string  $encoding  (optional) Language to look for.
	 * @return  mixed
	 */
	
	public static function acceptsEncoding($encoding = null)
	{
		return static::accept('HTTP_ACCEPT_ENCODING', $encoding);
	}
}

/** -------------------- End of file -------------------- **/