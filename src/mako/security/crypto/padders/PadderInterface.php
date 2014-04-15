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
	public function addPadding($string, $blockSize);

	public function stripPadding($string);
}