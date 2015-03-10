<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security;

/**
 * Secure comparison.
 *
 * @author  Frederic G. Østby
 */

class Comparer
{
	/**
	 * Timing attack safe string comparison. Returns TRUE if the two strings are equal and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $string1  String one
	 * @param   string   $string2  String two
	 * @return  boolean
	 */

	public static function compare($string1, $string2)
	{
		$string1 = (string) $string1;
		$string2 = (string) $string2;

		$string1Length = strlen($string1);
		$string2Length = strlen($string2);

		$minLength = min($string1Length, $string2Length);

		$result = $string1Length ^ $string2Length;

		for($i = 0; $i < $minLength; $i++)
		{
			$result |= ord($string1[$i]) ^ ord($string2[$i]);
		}

		return $result === 0;
	}
}