<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\writer;

/**
 * Standard writer.
 */
class Standard extends Writer
{
	/**
	 * Constructor.
	 *
	 * @param resource $stream Output stream
	 */
	public function __construct($stream = STDOUT)
	{
		$this->stream = $stream;
	}
}
