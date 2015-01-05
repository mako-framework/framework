<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\output\writer;

use \mako\cli\output\writer\WriterInterface;

/**
 * Error writer.
 *
 * @author  Frederic G. Østby
 */

class Error implements WriterInterface
{
	/**
	 * {@inheritdoc}
	 */
	 
	public function write($string)
	{
		fwrite(STDERR, $string);
	}
}