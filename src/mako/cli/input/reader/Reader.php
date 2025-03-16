<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\reader;

use function fgetc;
use function fgets;
use function fread;
use function trim;

/**
 * Reader.
 */
class Reader implements ReaderInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function read(): string
	{
		return trim(fgets(STDIN));
	}

	/**
	 * {@inheritDoc}
	 */
	public function readCharacter(): string
	{
		return fgetc(STDIN);
	}

	/**
	 * {@inheritDoc}
	 */
	public function readBytes(int $length): string
	{
		return fread(STDIN, $length);
	}
}
