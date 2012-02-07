<?php

namespace mako;

use \mako\I18n;
use \RuntimeException;

/**
* Helper class for working with CLI.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class CLI
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Text colors.
	*
	* @var array
	*/

	protected static $textColors = array
	(
		'black'  => '0;30',
		'red'    => '0;31',
		'green'  => '0;32',
		'yellow' => '0;33',
		'blue'   => '0;34',
		'purple' => '0;35',
		'cyan'   => '0;36',
		'white'  => '0;37',
	);

	/**
	* Background colors.
	*
	* @var array
	*/

	protected static $backgroundColors = array
	(
		'black'  => '40',
		'red'    => '41',
		'green'  => '42',
		'yellow' => '43',
		'blue'   => '44',
		'purple' => '45',
		'cyan'   => '46',
		'white'  => '47',
	);

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
	* Returns the screen size.
	*
	* @access  public
	* @return  array
	*/

	public function screenSize()
	{
		$size = array('width' => 0, 'height' => 0);

		if(function_exists('ncurses_getmaxyx'))
		{
			ncurses_getmaxyx(STDSCR, $size['height'], $size['width']);
		}
		else
		{
			if(!MAKO_IS_WINDOWS)
			{
				$size['width']  = exec('tput cols');
				$size['height'] = exec('tput lines');
			}
		}

		return $size;
	}

	/**
	* Add text color and background color to a string.
	*
	* @access  public
	* @param   string  String to colorize
	* @param   string  (optional) Text color name
	* @param   string  (optional) Background color name
	* @return  string
	*/

	public static function color($str, $textColor = null, $backgroundColor = null)
	{
		if(MAKO_IS_WINDOWS)
		{
			return $str;
		}
		
		$color = "";

		if($textColor !== null)
		{
			if(!isset(static::$textColors[$textColor]))
			{
				throw new RuntimeException(vsprintf("%s(): Invalid text color.", array(__METHOD__)));
			}

			$color .= "\033[" . static::$textColors[$textColor] . "m";
		}

		if($backgroundColor !== null)
		{
			if(!isset(static::$backgroundColors[$backgroundColor]))
			{
				throw new RuntimeException(vsprintf("%s(): Invalid background color.", array(__METHOD__)));
			}

			$color .= "\033[" . static::$backgroundColors[$backgroundColor] . "m";
		}

		return $color . $str . "\033[0m";
	}

	/**
	* Return value of named parameters (--<name>=<value>).
	*
	* @access public
	* @param  string  Parameter name
	* @param  string  (optional) Default value
	* @return string
	*/

	public static function param($name, $default = null)
	{
		static $parameters = false;

		// Only parse parameters once

		if($parameters === false)
		{
			$parameters = array();

			foreach($_SERVER['argv'] as $arg)
			{
				if(substr($arg, 0, 2) === '--')
				{
					$arg = explode('=', substr($arg, 2), 2);

					$parameters[$arg[0]] = isset($arg[1]) ? $arg[1] : true;
				}
			}	
		}

		return isset($parameters[$name]) ? $parameters[$name] : $default;
	}

	/**
	* Prompt user for input.
	*
	* @access  public
	* @param   string  Question for the user
	* @return  string
	*/

	public static function input($question)
	{
		fwrite(STDOUT, $question . ': ');

		return trim(fgets(STDIN));
	}

	/**
	* Prompt user a confirmation.
	*
	* @access  public
	* @param   string   Question for the user
	* @return  boolean
	*/

	public static function confirm($question)
	{
		fwrite(STDOUT, $question . ' [' . I18n::getText('Y') . '/' . I18n::getText('N') . ']: ');

		$input = trim(fgets(STDIN));

		switch(mb_strtoupper($input))
		{
			case I18n::getText('Y'):
				return true;
			break;
			case I18n::getText('N'):
				return false;
			break;
			default:
				return static::confirm($question);
		}
	}

	/**
	* Print message to STDOUT.
	*
	* @access  public
	* @param   string  (optional) Message to print
	* @param   string  (optional) Text color
	* @param   string  (optional) Background color
	*/

	public static function stdout($message = '', $textColor = null, $backgroundColor = null)
	{
		fwrite(STDOUT, static::color($message, $textColor, $backgroundColor) . PHP_EOL);
	}

	/**
	* Print message to STDERR.
	*
	* @access  public
	* @param   string  Message to print
	* @param   string  (optional) Text color
	* @param   string  (optional) Background color
	*/

	public static function stderr($message, $textColor = 'red', $backgroundColor = null)
	{
		fwrite(STDERR, static::color($message, $textColor, $backgroundColor) . PHP_EOL);	
	}

	/**
	* Sytem Beep.
	*
	* @access  public
	* @param   int     (optional) Number of system beeps
	*/

	public static function beep($beeps = 1)
	{
		fwrite(STDOUT, str_repeat("\x07", $beeps));
	}

	/**
	* Display countdown for n seconds.
	*
	* @access  public
	* @param   int      Number of seconds to wait
	* @param   boolean  (optional) Enable beep?
	*/

	public static function wait($seconds = 5, $withBeep = false)
	{
		$length = strlen($seconds);

		while($seconds > 0)
		{
			fwrite(STDOUT, "\r" . I18n::getText('Please wait ...') . ' [ ' . str_pad($seconds--, $length, 0, STR_PAD_LEFT) . ' ]');

			if($withBeep === true)
			{
				static::beep();	
			}

			sleep(1);
		}

		fwrite(STDOUT, "\r\033[0K");
	}
}

/** -------------------- End of file --------------------**/