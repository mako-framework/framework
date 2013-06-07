<?php

namespace mako;

use \mako\HTML;

/**
 * Collection of string manipulation methods.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class String
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Alphanumeric characters.
	 *
	 * @var string
	 */

	const ALNUM = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 * Alphabetic characters.
	 *
	 * @var string
	 */

	const ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 * Hexadecimal characters.
	 *
	 * @var string
	 */
	
	const HEXDEC = '0123456789abcdef';

	/**
	 * Numeric characters.
	 *
	 * @var string
	 */

	const NUMERIC = '0123456789';

	/**
	 * ASCII symbols.
	 *
	 * @var string
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
	 * Replaces newline with <br> or <br />.
	 *
	 * @access  public
	 * @param   string  $string  The input string
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
	 * @param   string  $string  The input string
	 * @return  string
	 */

	public static function br2nl($string)
	{
		return str_replace(array('<br>', '<br/>', '<br />'), "\n", $string);
	}

	/**
	 * Converts camel case to underscored.
	 *
	 * @access  public
	 * @param   string  $string  The input string
	 * @return  string
	 */

	public static function camel2underscored($string)
	{
		return mb_strtolower(preg_replace('/([^A-Z])([A-Z])/u', "$1_$2", $string));
	}

	/**
	 * Converts underscored to camel case.
	 *
	 * @access  public
	 * @param   string   $string  The input string
	 * @param   boolean  $upper   (optional) Return upper case camelCase?
	 * @return  string
	 */

	public static function underscored2camel($string, $upper = false)
	{
		return preg_replace_callback(($upper ? '/(?:^|_)(.?)/u' : '/_(.?)/u'), function($matches){ return mb_strtoupper($matches[1]); }, $string);
	}

	/**
	 * Limits the number of characters in a string.
	 *
	 * @access  public
	 * @param   string  $string      The input string
	 * @param   int     $characters  (optional) Number of characters to allow
	 * @param   string  $sufix       (optional) Sufix to add if number of characters is reduced
	 */

	public static function limitChars($string, $characters = 100, $sufix = '...')
	{
		return (mb_strlen($string) > $characters) ? trim(mb_substr($string, 0, $characters)) . $sufix : $string;
	}

	/**
	 * Limits the number of words in a string.
	 *
	 * @access  public
	 * @param   string  $string  The input string
	 * @param   int     $words   (optional) Number of words to allow
	 * @param   string  $sufix   (optional) Sufix to add if number of words is reduced
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
	 * @param   string  $string  The input string
	 * @return  string
	 */

	public static function slug($string)
	{
		return mb_strtolower(preg_replace('/\s{1,}/', '-', trim(preg_replace('/[\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7E]/', '', $string))));
	}

	/**
	 * Strips all non-ASCII characters.
	 *
	 * @access  public
	 * @param   string  $string  The input string
	 * @return  string
	 */

	public static function ascii($string)
	{
		return preg_replace('/[^\x0-\x7F]/', '', $string);
	}

	/**
	 * Returns a closure that will alternate between the defined strings.
	 *
	 * @access  public
	 * @param   array    $strings  Array of strings to alternate between
	 * @return  Closure
	 */

	public static function alternator(array $strings)
	{
		return function() use ($strings)
		{
			static $i = 0;

			return $strings[($i++ % count($strings))];
		};
	}
	
	/**
	 * Converts URLs in a text into clickable links.
	 *
	 * @access  public
	 * @param   string   $string      Text to scan for links
	 * @param   array    $attributes  (optional) Anchor attributes
	 * @return  string
	 */
	
	public static function autolink($string, array $attributes = array())
	{
		return preg_replace_callback('#\b(?<!href="|">)[a-z]+://\S+(?:/|\b)#i', function($matches) use ($attributes)
		{
			return HTML::tag('a', array('href' => $matches[0]) + $attributes, $matches[0]);
		}, $string);
	}

	/**
	 * Returns a masked string where only the last n characters are visible.
	 *
	 * @access  public
	 * @param   string  $string   String to mask
	 * @param   int     $visible  (optional) Number of characters to show
	 * @param   string  $mask     (optional) Character used to replace remaining characters
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
	 * @param   string  $string     String to increment
	 * @param   int     $start      Starting number
	 * @param   string  $separator  Separator
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
	 * @param   string  $pool    Character pool to use
	 * @param   int     $length  (optional) Desired string length
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

/** -------------------- End of file -------------------- **/