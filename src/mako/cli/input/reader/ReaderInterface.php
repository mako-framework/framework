<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\input\reader;

/**
 * Reader interface.
 *
 * @author  Frederic G. Østby
 */

interface ReaderInterface
{
	/**
	 * Reads and returns user input.
	 *
	 * @access  public
	 * @return  string
	 */

	public function read();
}