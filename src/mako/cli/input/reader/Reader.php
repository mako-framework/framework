<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\reader;

use Override;

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
	#[Override]
	public function read(): string
	{
		return trim(fgets(STDIN));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function readCharacter(): string
	{
		return fgetc(STDIN);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function readBytes(int $length): string
	{
		return fread(STDIN, $length);
	}
}
