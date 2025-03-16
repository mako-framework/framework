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

	/**
	 * Reads and returns a single character.
	 */
	public function readCharacter(): string;

	/**
	 * Reads and returns a specified number of bytes.
	 */
	public function readBytes(int $length): string;
}
