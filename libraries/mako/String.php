<?php

namespace mako
{
	/**
	* Collection of string manipulation methods.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class String
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
		* Replaces newline with <br />.
		*
		* @access  public
		* @param   string  The input string
		* @return  string
		*/

		public static function nl2Br($string)
		{
			return str_replace(array("\r\n", "\n\r", "\n", "\r"), '<br />', $string);
		}

		/**
		* Replaces <br> and <br /> with newline.
		*
		* @access  public
		* @param   string  The input string
		* @return  string
		*/

		public static function br2Nl($string)
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
		* Returns a random string of the selected type and length.
		*
		* @param   string  Type of character pool to use or user defined character pool
		* @param   int     (optional) Desired string length
		* @return  string
		*/

		public static function random($type = 'alnum', $length = 16)
		{
			switch($type)
			{
				case 'alnum':
					$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
				case 'alpha':
					$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;
				case 'hexdec':
					$pool = '0123456789abcdef';
				break;
				case 'numeric':
					$pool = '0123456789';
				break;
				default:
					$pool = (string) $type;
			}

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