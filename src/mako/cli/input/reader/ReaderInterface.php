<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\reader;

/**
 * Reader interface.
 */
interface ReaderInterface
{
	/**
	 * Reads and returns user input.
	 */
	public function read(): string;
}
