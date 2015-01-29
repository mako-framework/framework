<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\security\crypto\padders;

/**
 * Padder interface.
 *
 * @author  Frederic G. Østby
 */

interface PadderInterface
{
	/**
	 * Adds padding to string.
	 *
	 * @access  public
	 * @param   string     $string     String we want to pad
	 * @param   int        $blockSize  Block size
	 * @return  string
	 */

	public function addPadding($string, $blockSize);

	/**
	 * Removes padding from string.
	 *
	 * @access  public
	 * @param   string          $string  String we want to unpad
	 * @return  string|boolean
	 */

	public function stripPadding($string);
}