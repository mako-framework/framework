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
	 */
	public function format(string $string): string;

	/**
	 * Returns a string where all formatting tags have been escaped.
	 */
	public function escape(string $string): string;

	/**
	 * Returns a string where all formatting tags have been stripped.
	 */
	public function stripTags(string $string): string;
}
