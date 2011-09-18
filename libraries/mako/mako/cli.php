<?php

namespace mako
{
	use \RuntimeException;

	/**
	* Helper class for working with CLI.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
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
			'white'  => '0;37',
			'green'  => '0;32',
			'red'    => '0;31',
			'blue'   => '0;34',
			'cyan'   => '0;36',
			'yellow' => '0;33',
			'purple' => '0;35',
		);

		/**
		* Background colors.
		*/

		protected static $backgroundColors = array
		(
			'black'  => '40',
			'white'  => '47',
			'green'  => '42',
			'red'    => '41',
			'blue'   => '44',
			'cyan'   => '46',
			'yellow' => '43',
			'purple' => '45',
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
			$color = "";

			if($textColor !== null)
			{
				if(!isset(static::$textColors[$textColor]))
				{
					throw new RuntimeException(__CLASS__ . ": Invalid text color.");
				}

				$color .= "\033[" . static::$textColors[$textColor] . "m";
			}

			if($backgroundColor !== null)
			{
				if(!isset(static::$backgroundColors[$backgroundColor]))
				{
					throw new RuntimeException(__CLASS__ . ": Invalid background color.");
				}

				$color .= "\033[" . static::$backgroundColors[$backgroundColor] . "m";
			}

			return $color . $str . "\033[0m";
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
			fwrite(STDOUT, $question . '? [Y/N]: ');

			$input = trim(fgets(STDIN));

			switch($input)
			{
				case 'y':
				case 'Y':
					return true;
				break;
				case 'n':
				case 'N':
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
		* @param   string  Message to print
		* @param   string  (optional) Text color
		* @param   string  (optional) Background color
		*/

		public static function stdout($message, $textColor = null, $backgroundColor = null)
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
		* param    int      Number of seconds to wait
		* param    boolean  (optional) Enable beep?
		*/

		public static function wait($seconds = 5, $withBeep = false)
		{
			while($seconds > 0)
			{
				fwrite(STDOUT, "\rPlease wait ... [ " . $seconds-- . " ] ");

				if($withBeep === true)
				{
					static::beep();	
				}

				sleep(1);
			}

			fwrite(STDOUT, "\r");
		}
	}
}

/** -------------------- End of file --------------------**/