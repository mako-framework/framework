<?php

namespace mako;

use \InvalidArgumentException;

/**
 * Class containing number helper methods.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Num
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
	 * Converts arabic numerals (1-3999) to roman numerals.
	 *
	 * @access  public
	 * @param   int     $int  Arabic numeral to convert
	 * @return  string
	 */

	public static function arabic2roman($int)
	{
		$int = (int) $int;

		if($int < 1 || $int > 3999)
		{
			throw new InvalidArgumentException(vsprintf("%s(): The number must be between 1 and 3999.", array(__METHOD__)));
		}

		$numerals = array
		(
			'M'  => 1000,
			'CM' => 900,
			'D'  => 500,
			'CD' => 400,
			'C'  => 100,
			'XC' => 90,
			'L'  => 50,
			'XL' => 40,
			'X'  => 10,
			'IX' => 9,
			'V'  => 5,
			'IV' => 4,
			'I'  => 1
		);

		$romanNumeral = '';

		foreach($numerals as $roman => $arabic)
		{
			$count = (int) ($int / $arabic);

			if($count === 0)
			{
				continue;
			}

			$romanNumeral .= str_repeat($roman, $count);

			$int %= $arabic;

			if($int === 0)
			{
				break;
			}
		}

		return $romanNumeral;
	}

	/**
	 * Converts roman numerals (I-MMMCMXCIX) to arabic numerals.
	 *
	 * @access  public
	 * @param   string  $str  Roman numeral to convert
	 * @return  int
	 */

	public static function roman2arabic($str)
	{
		if(empty($str) || preg_match('/^M{0,3}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$/i', $str) === 0)
		{
			throw new InvalidArgumentException(vsprintf("%s(): Invalid roman numeral. Only values between I and MMMCMXCIX are allowed.", array(__METHOD__)));
		}

		$numerals = array
		(
			'M'  => 1000,
			'D'  => 500,
			'C'  => 100,
			'L'  => 50,
			'X'  => 10,
			'V'  => 5,
			'I'  => 1
		);

		$arabicNumeral = 0;

		$previous = 0;

		foreach(str_split($str) as $numeral)
		{
			$arabicNumeral += $numerals[$numeral];

			if($previous < $numerals[$numeral])
			{
				$arabicNumeral -= ($previous * 2);
			}

			$previous = $numerals[$numeral];
		}

		return $arabicNumeral;
	}
}

/** -------------------- End of file -------------------- **/