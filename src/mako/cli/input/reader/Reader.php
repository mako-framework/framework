<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\input\reader;

use mako\cli\input\reader\ReaderInterface;

/**
 * Reader.
 *
 * @author  Frederic G. Østby
 */

class Reader implements ReaderInterface
{
	/**
	 * {@inheritdoc}
	 */

	public function read()
	{
		return trim(fgets(STDIN));
	}
}