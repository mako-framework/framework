<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\reader;

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
