<?php

namespace mako\reactor;

use \mako\I18n;
use \RuntimeException;

/**
 * Helper class for working with CLI.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class CLI
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Text colors.
	 *
	 * @var array
	 */

	protected $foregroundColors = array
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

	protected $backgroundColors = array
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
	 * Text styles.
	 *
	 * @var array
	 */

	protected $textStyles = array
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
	 * Constructor.
	 *
	 * @access  public
	 */

	public function __construct()
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
				$size['width']  = (int) exec('tput cols');
				$size['height'] = (int) exec('tput lines');
			}
		}

		return $size;
	}

	/**
	 * Returns the screen width.
	 * 
	 * @access  public
	 * @return  int
	 */

	public function screenWidth()
	{
		$size = $this->screenSize();

		return $size['width'];
	}

	/**
	 * Returns the screen height.
	 * 
	 * @access  public
	 * @return  int
	 */

	public function screenHeight()
	{
		$size = $this->screenSize();

		return $size['height'];
	}

	/**
	 * Add text color and background color to a string.
	 *
	 * @access  public
	 * @param   string  $str         String to colorize
	 * @param   string  $foreground  (optional) Text color name
	 * @param   string  $background  (optional) Background color name
	 * @return  string
	 */

	public function color($str, $foreground = null, $background = null)
	{
		if(MAKO_IS_WINDOWS)
		{
			return $str;
		}

		$ansiCodes = array();

		// Font color

		if($foreground !== null)
		{
			if(!isset($this->foregroundColors[$foreground]))
			{
				throw new RuntimeException(vsprintf("%s(): Invalid text color. Only the following colors are valid: %s.", array
				(
					__METHOD__,
					implode(', ', array_keys($this->foregroundColors))
				)));
			}

			$ansiCodes[] = $this->foregroundColors[$foreground];
		}

		// Background color

		if($background !== null)
		{
			if(!isset($this->backgroundColors[$background]))
			{
				throw new RuntimeException(vsprintf("%s(): Invalid background color.  Only the following colors are valid: %s.", array
				(
					__METHOD__,
					implode(', ', array_keys($this->backgroundColors))
				)));
			}

			$ansiCodes[] = $this->backgroundColors[$background];
		}

		return sprintf("\033[%sm%s\033[0m", implode(';', $ansiCodes), $str);
	}

	/**
	 * Add styles to a string.
	 *
	 * @access  public
	 * @param   string  $str      String to style 
	 * @param   array   $styles  (optional) Text styles
	 * @return  string
	 */

	public function style($str, array $styles)
	{
		if(MAKO_IS_WINDOWS)
		{
			return $str;
		}

		$ansiCodes = array();

		foreach($styles as $style)
		{
			if(!isset($this->textStyles[$style]))
			{
				throw new RuntimeException(vsprintf("%s(): Invalid text option. Only the following styles are valid: %s.", array
				(
					__METHOD__,
					implode(', ', array_keys($this->textStyles))
				)));
			}

			$ansiCodes[] = $this->textStyles[$style];
		}

		return sprintf("\033[%sm%s\033[0m", implode(';', $ansiCodes), $str); 
	}

	/**
	 * Return value of named parameters (--<name>=<value>).
	 *
	 * @access public
	 * @param  string  $name     Parameter name
	 * @param  string  $default  (optional) Default value
	 * @return string
	 */

	public function param($name, $default = null)
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

	public function input($question)
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

	public function confirm($question)
	{
		fwrite(STDOUT, $question . ' [Y/N]: ');

		$input = trim(fgets(STDIN));

		switch(strtoupper($input))
		{
			case 'Y':
				return true;
			break;
			case 'N':
				return false;
			break;
			default:
				return $this->confirm($question);
		}
	}

	/**
	 * Print message to STDOUT.
	 *
	 * @access  public
	 * @param   string  $message     (optional) Message to print
	 * @param   string  $foreground  (optional) Text color
	 * @param   string  $background  (optional) Background color
	 * @param   array   $styles      (optional) Text styles
	 */

	public function stdout($message = '', $foreground = null, $background = null, array $styles = array())
	{
		fwrite(STDOUT, $this->style($this->color($message, $foreground, $background), $styles) . PHP_EOL);
	}

	/**
	 * Print message to STDERR.
	 *
	 * @access  public
	 * @param   string  $message     Message to print
	 * @param   string  $foreground  (optional) Text color
	 * @param   string  $background  (optional) Background color
	 * @param   array   $styles      (optional) Text styles
	 */

	public function stderr($message, $foreground = 'red', $background = null, array $styles = array())
	{
		fwrite(STDERR, $this->style($this->color($message, $foreground, $background), $styles) . PHP_EOL);	
	}

	/**
	 * Outputs n empty lines.
	 * 
	 * @access  public
	 * @param   int     $lines  Number of empty lines
	 */

	public function newLine($lines = 1)
	{
		fwrite(STDOUT, str_repeat(PHP_EOL, $lines));
	}

	/**
	 * Clears the screen.
	 * 
	 * @access  public
	 */

	public function clearScreen()
	{
		if(MAKO_IS_WINDOWS)
		{
			$this->newLine(50);
		}
		else
		{
			fwrite(STDOUT, "\033[H\033[2J");
		}
	}

	/**
	 * Sytem Beep.
	 *
	 * @access  public
	 * @param   int     $beeps  (optional) Number of system beeps
	 */

	public function beep($beeps = 1)
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

	public function wait($seconds = 5, $withBeep = false)
	{
		$length = strlen($seconds);

		while($seconds > 0)
		{
			fwrite(STDOUT, "\r" . 'Please wait ... [ ' . $this->color(str_pad($seconds--, $length, 0, STR_PAD_LEFT), 'yellow') . ' ]');

			if($withBeep === true)
			{
				$this->beep();	
			}

			sleep(1);
		}

		fwrite(STDOUT, "\r\033[0K");
	}
}

/** -------------------- End of file -------------------- **/