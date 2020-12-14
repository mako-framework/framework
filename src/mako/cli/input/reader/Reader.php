<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\reader;

use function fgets;
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
}
