<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\output\writer;

use \mako\cli\output\writer\WriterInterface;

/**
 * Standard writer.
 *
 * @author  Frederic G. Østby
 */

class Standard implements WriterInterface
{
	/**
	 * {@inheritdoc}
	 */
	 
	public function write($string)
	{
		fwrite(STDOUT, $string);
	}
}