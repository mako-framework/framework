<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto\padders;

/**
 * PKCS7 padder.
 *
 * @author  Frederic G. Østby
 */

class PKCS7 implements \mako\security\crypto\padders\PadderInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Adds PKCS7 padding to string.
	 * 
	 * @access  public
	 * @param   string     $string     String we want to pad
	 * @param   int        $blockSize  Block size
	 * @return  string
	 */

	public function addPadding($string, $blockSize)
	{
		$padSize = $blockSize - (strlen($string) % $blockSize);

		return $string . str_repeat(chr($padSize), $padSize);
	}

	/**
	 * Removes PKCS7 padding from string.
	 * 
	 * @access  public
	 * @param   string          $string  String we want to unpad
	 * @return  string|boolean
	 */

	public function stripPadding($string)
	{
		$padChar = substr($string, -1);

		$padSize = ord($padChar);

		$stringLength = strlen($string) - $padSize;

		if(substr($string, $stringLength) === str_repeat($padChar, $padSize))
		{
			return substr($string, 0, $stringLength);
		}

		return false;
	}
}