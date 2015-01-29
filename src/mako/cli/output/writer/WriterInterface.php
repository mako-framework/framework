<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\output\writer;

/**
 * Writer interface.
 *
 * @author  Frederic G. Østby
 */

interface WriterInterface
{
	/**
	 * Returns TRUE if the output isn't redirected or piped and FALSE in all other situations.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function isDirect();

	/**
	 * Writes output.
	 *
	 * @access  public
	 * @param   string  $string  String to write
	 */

	public function write($string);
}