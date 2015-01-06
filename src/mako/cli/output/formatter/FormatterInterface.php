<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\output\formatter;

/**
 * Formatter interface.
 *
 * @author  Frederic G. Østby
 */

interface FormatterInterface
{
	/**
	 * Returns formatted string.
	 * 
	 * @access  public
	 * @param   string  $string  String to format
	 * @return  string
	 */

	public function format($string);

	/**
	 * Strips formatting tags.
	 * 
	 * @access  public
	 * @param   string  $string  String to strip
	 * @return  string
	 */

	public function stripFormatting($string);
}