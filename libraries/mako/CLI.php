<?php

namespace mako
{
	use \mako\Mako;
	use \mako\I18n;
	use \mako\Request;
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
		* Add text color and background color to a string.
		*
		* @access  protected
		* @param   string     String to colorize
		* @param   string     Text color name
		* @param   string     Background color name
		* @return  string
		*/

		protected static function color($str, $textColor, $backgroundColor)
		{
			if(Mako::isWindows())
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

		public static function stderr($message, $textColor = 'white', $backgroundColor = 'red')
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
		* @param    int      Number of seconds to wait
		* @param    boolean  (optional) Enable beep?
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
}

/** -------------------- End of file --------------------**/