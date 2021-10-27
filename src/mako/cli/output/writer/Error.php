<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\writer;

/**
 * Error writer.
 */
class Error extends Writer
{
	/**
	 * Constructor.
	 *
	 * @param resource $stream Output stream
	 */
	public function __construct($stream = STDERR)
	{
		$this->stream = $stream;
	}
}
