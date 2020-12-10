<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\formatter;

/**
 * Formatter interface.
 */
interface FormatterInterface
{
	/**
	 * Returns formatted string.
	 *
	 * @param  string $string String to format
	 * @return string
	 */
	public function format(string $string): string;

	/**
	 * Returns a string where all formatting tags have been escaped.
	 *
	 * @param  string $string String to format
	 * @return string
	 */
	public function escape(string $string): string;

	/**
	 * Returns a string where all formatting tags have been stripped.
	 *
	 * @param  string $string String to strip
	 * @return string
	 */
	public function stripTags(string $string): string;
}
