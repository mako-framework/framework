<?php

namespace mako
{
	use \mako\HTML;

	/**
	* Collection of string manipulation methods.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class String
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Alphanumeric characters.
		*/

		const ALNUM = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		/**
		* Alphabetic characters.
		*/

		const ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

		/**
		* Hexadecimal characters.
		*/
		
		const HEXDEC = '0123456789abcdef';

		/**
		* Numeric characters.
		*/

		const NUMERIC = '0123456789';

		/**
		* ASCII symbols.
		*/

		const SYMBOLS = '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~';

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
		* Alias of htmlspecialchars but it also works on arrays.
		*
		* @access  public
		* @param   mixed    String or array to encode
		* @param   boolean  (optional) True to enable double encoding of entities and false to disable
		* @return  mixed
		*/

		public static function specialCharsEncode($input, $doubleEncode = true)
		{
			if(is_array($input))
			{
				foreach($input as $k => $v)
				{
					$input[$k] = static::specialCharsEncode($v, $doubleEncode);
				}
			}
			else
			{
				$input = htmlspecialchars($input, ENT_COMPAT, mb_internal_encoding(), $doubleEncode);
			}

			return $input;
		}

		/**
		* Alias of htmlspecialchars_decode but it also works on arrays.
		*
		* @access  public
		* @param   mixed  String or array to decode
		* @return  mixed
		*/

		public static function specialCharsDecode($input)
		{
			if(is_array($input))
			{
				foreach($input as $k => $v)
				{
					$input[$k] = static::specialCharsDecode($v);
				}
			}
			else
			{
				$input = htmlspecialchars_decode($input, ENT_COMPAT);
			}

			return $input;
		}

		/**
		* Replaces newline with <br> or <br />.
		*
		* @access  public
		* @param   string  The input string
		* @return  string
		*/

		public static function nl2br($string)
		{
			return str_replace(array("\r\n", "\n\r", "\n", "\r"), HTML::tag('br'), $string);
		}

		/**
		* Replaces <br> and <br /> with newline.
		*
		* @access  public
		* @param   string  The input string
		* @return  string
		*/

		public static function br2nl($string)
		{
			return str_replace(array('<br>', '<br />'), "\n", $string);
		}

		/**
		* Converts & to &amp;.
		*
		* @access  public
		* @param   string  The input string
		* @return  string
		*/

		public static function ampEncode($string)
		{
			return str_replace('&', '&amp;', $string);
		}

		/**
		* Converts &amp; to &.
		*
		* @access  public
		* @param   string  The input string
		* @return  string
		*/

		public static function ampDecode($string)
		{
			return str_replace('&amp;', '&', $string);
		}

		/**
		* Limits the number of characters in a string.
		*
		* @access  public
		* @param   string  The input string
		* @param   int     (optional) Number of characters to allow
		* @param   string  (optional) Sufix to add if number of characters is reduced
		*/

		public static function limitChars($string, $characters = 100, $sufix = '...')
		{
			return (mb_strlen($string) > $characters) ? trim(mb_substr($string, 0, $characters)) . $sufix : $string;
		}

		/**
		* Limits the number of words in a string.
		*
		* @access  public
		* @param   string  The input string
		* @param   int     (optional) Number of words to allow
		* @param   string  (optional) Sufix to add if number of words is reduced
		*/

		public static function limitWords($string, $words = 100, $sufix = '...')
		{
			preg_match('/^\s*+(?:\S++\s*+){1,' . $words . '}/', $string, $matches);

			if(isset($matches[0]) && mb_strlen($matches[0]) < mb_strlen($string))
			{
				return trim($matches[0]) . $sufix;
			}

			return $string;
		}

		/**
		* Creates url friendly string.
		*
		* @access  public
		* @param   string  The input string
		* @return  string
		*/

		public static function urlFriendly($string)
		{
			return mb_strtolower(preg_replace('/\s{1,}/', '-', trim(preg_replace('/[\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7E]/', '', $string))));
		}

		/**
		* Strips all non-ASCII characters.
		*
		* @access  public
		* @param   string  The input string
		* @return  string
		*/

		public static function ascii($string)
		{
			return preg_replace('/[^\x0-\x7F]/', '', $string);
		}

		/**
		* Alternates between two or more strings.
		*
		* @access  public
		* @param   array    Array of strings to alternate between
		* @param   boolean  (optional) Reset alternator?
		* @return  string
		*/

		public static function alternate(array $strings, $reset = false)
		{
			static $i = 0;

			$reset && $i = 0;

			return $strings[($i++ % count($strings))];
		}
		
		/**
		* Converts URLs in a text into clickable links.
		*
		* @access  public
		* @param   string   Text to scan for links
		* @param   boolean  (optional) Set to TRUE to add the "nofollow" attribute to the links
		* @return  string
		*/
		
		public static function autoLink($string, $noFollow = false)
		{
			return preg_replace('#\b(?<!href="|">)[a-z]+://\S+(?:/|\b)#i', ($noFollow ? '<a href="${0}" rel="nofollow">${0}</a>' : '<a href="${0}">${0}</a>'), $string);
		}

		/**
		* Returns a masked string where only the last n characters are visible.
		*
		* @access  public
		* @param   string  String to mask
		* @param   int     (optional) Number of characters to show
		* @param   string  (optional) Character used to replace remaining characters
		* @return  string
		*/

		public static function mask($string, $visible = 3, $mask = '*')
		{
			$substr = mb_substr($string, -$visible);

			return str_pad($substr, (mb_strlen($string) + (strlen($substr) - mb_strlen($substr))), $mask, STR_PAD_LEFT);
		}

		/**
		* Increments a string by appending a number to it or increasing the number.
		*
		* @access  public
		* @param   string  String to increment
		* @param   int     Starting number
		* @param   string  Separator
		* @return  string
		*/

		public static function increment($string, $start = 1, $separator = '_')
		{
			preg_match('/(.+)' . preg_quote($separator) . '([0-9]+)$/', $string, $matches);

			return isset($matches[2]) ? $matches[1] . $separator . ((int) $matches[2] + 1) : $string . $separator . $start;
		}

		/**
		* Returns a random string of the selected type and length.
		*
		* @param   string  Character pool to use
		* @param   int     (optional) Desired string length
		* @return  string
		*/

		public static function random($pool = String::ALNUM, $length = 32)
		{
			$string = '';

			$poolSize = mb_strlen($pool) - 1;

			for($i = 0; $i < $length; $i++)
			{
				$string .= mb_substr($pool, mt_rand(0, $poolSize), 1);
			}

			return $string;
		}
	}
}

/** -------------------- End of file --------------------**/