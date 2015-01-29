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
	 * Returns a string where all formatting tags have been escaped.
	 *
	 * @access  public
	 * @param   string  $string  String to format
	 * @return  string
	 */

	public function escape($string);

	/**
	 * Returns a string where all formatting tags have been stripped.
	 *
	 * @access  public
	 * @param   string  $string  String to strip
	 * @return  string
	 */

	public function strip($string);
}