<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\reader;

/**
 * Reader interface.
 *
 * @author Frederic G. Østby
 */
interface ReaderInterface
{
	/**
	 * Reads and returns user input.
	 *
	 * @return string
	 */
	public function read(): string;
}
