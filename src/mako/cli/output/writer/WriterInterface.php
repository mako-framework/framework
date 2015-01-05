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
	 * Writes output.
	 * 
	 * @access  public
	 * @param   string  $string  String to write
	 */
	 
	public function write($string);
}