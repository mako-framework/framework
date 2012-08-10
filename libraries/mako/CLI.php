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
		'black'  => '30',
		'red'    => '31',
		'green'  => '32',
		'yellow' => '33',
		'blue'   => '34',
		'purple' => '35',
		'cyan'   => '36',
		'white'  => '37',
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

	/**
	* Text options.
	*
	* @var array
	*/

	protected static $textOptions = array
	(
		'bold'       => 1,
		'faded'      => 2,
		'underlined' => 4,
		'blinking'   => 5,
		'reversed'   => 7,
		'hidden'     => 8,
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

	public static function screenSize()
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
	* @param   string  $str              String to colorize
	* @param   string  $textColor        (optional) Text color name
	* @param   string  $backgroundColor  (optional) Background color name
	* @param   array   $textOptions      (optional) Text options
	* @return  string
	*/

	public static function color($str, $textColor = null, $backgroundColor = null, array $textOptions = array())
	{
		if(MAKO_IS_WINDOWS)
		{
			return $str;
		}

		$style = array();

		// Font color

		if($textColor !== null)
		{
			if(!isset(static::$textColors[$textColor]))
			{
				throw new RuntimeException(vsprintf("%s(): Invalid text color. Only the following colors are valid: %s.", array
				(
					__METHOD__,
					implode(', ', array_keys(static::$textColors))
				)));
			}

			$style[] = static::$textColors[$textColor];
		}

		// Background color

		if($backgroundColor !== null)
		{
			if(!isset(static::$backgroundColors[$backgroundColor]))
			{
				throw new RuntimeException(vsprintf("%s(): Invalid background color.  Only the following colors are valid: %s.", array
				(
					__METHOD__,
					implode(', ', array_keys(static::$backgroundColors))
				)));
			}

			$style[] = static::$backgroundColors[$backgroundColor];
		}

		// Text options

		if($textOptions !== null)
		{
			$options = array();

			foreach($textOptions as $option)
			{
				if(!isset(static::$textOptions[$option]))
				{
					throw new RuntimeException(vsprintf("%s(): Invalid text option. Only the following options are valid: %s.", array
					(
						__METHOD__,
						implode(', ', array_keys(static::$textOptions))
					)));
				}

				$options[] = static::$textOptions[$option];
			}

			$style = array_merge($style, $options);
		}

		// Wrap text in style "tags"

		return sprintf("\033[%sm%s\033[0m", implode(';', $style), $str);
	}

	/**
	* Return value of named parameters (--<name>=<value>).
	*
	* @access public
	* @param  string  $name     Parameter name
	* @param  string  $default  (optional) Default value
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
	* @param   string  $question  Question for the user
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
	* @param   string   $question  Question for the user
	* @return  boolean
	*/

	public static function confirm($question)
	{
		fwrite(STDOUT, $question . ' [' . I18n::translate('Y') . '/' . I18n::translate('N') . ']: ');

		$input = trim(fgets(STDIN));

		switch(mb_strtoupper($input))
		{
			case I18n::translate('Y'):
				return true;
			break;
			case I18n::translate('N'):
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
	* @param   string  $message          (optional) Message to print
	* @param   string  $textColor        (optional) Text color
	* @param   string  $backgroundColor  (optional) Background color
	* @param   array   $textOptions      (optional) Text options
	*/

	public static function stdout($message = '', $textColor = null, $backgroundColor = null, array $textOptions = array())
	{
		fwrite(STDOUT, static::color($message, $textColor, $backgroundColor, $textOptions) . PHP_EOL);
	}

	/**
	* Print message to STDERR.
	*
	* @access  public
	* @param   string  $message          Message to print
	* @param   string  $textColor        (optional) Text color
	* @param   string  $backgroundColor  (optional) Background color
	* @param   array   $textOptions      (optional) Text options
	*/

	public static function stderr($message, $textColor = 'red', $backgroundColor = null, array $textOptions = array())
	{
		fwrite(STDERR, static::color($message, $textColor, $backgroundColor, $textOptions) . PHP_EOL);	
	}

	/**
	* Sytem Beep.
	*
	* @access  public
	* @param   int     $beeps  (optional) Number of system beeps
	*/

	public static function beep($beeps = 1)
	{
		fwrite(STDOUT, str_repeat("\x07", $beeps));
	}

	/**
	* Display countdown for n seconds.
	*
	* @access  public
	* @param   int      $seconds   Number of seconds to wait
	* @param   boolean  $withBeep  (optional) Enable beep?
	*/

	public static function wait($seconds = 5, $withBeep = false)
	{
		$length = strlen($seconds);

		while($seconds > 0)
		{
			fwrite(STDOUT, "\r" . I18n::translate('Please wait ...') . ' [ ' . str_pad($seconds--, $length, 0, STR_PAD_LEFT) . ' ]');

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