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
 *
 * @author Frederic G. Østby
 */
class Reader implements ReaderInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function read(): string
	{
		return trim(fgets(STDIN));
	}
}
