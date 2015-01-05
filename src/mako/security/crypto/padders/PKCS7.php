<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto\padders;

use mako\security\crypto\padders\PadderInterface;

/**
 * PKCS7 padder.
 *
 * @author  Frederic G. Østby
 */

class PKCS7 implements PadderInterface
{
	/**
	 * {@inheritdoc}
	 */

	public function addPadding($string, $blockSize)
	{
		$padSize = $blockSize - (strlen($string) % $blockSize);

		return $string . str_repeat(chr($padSize), $padSize);
	}

	/**
	 * {@inheritdoc}
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