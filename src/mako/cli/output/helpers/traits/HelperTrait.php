<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers\traits;

use function mb_strwidth;

/**
 * Helper trait.
 *
 * @property \mako\cli\output\formatter\FormatterInterface|null $formatter
 */
trait HelperTrait
{
	/**
	 * Returns the visible width of the string without formatting.
	 */
	protected function getVisibleStringWidth(string $string): int
	{
		// Strip tags if a formatter is set

		$string = $this->formatter !== null ? $this->formatter->stripTags($string) : $string;

		// Strip ANSI codes and OSC sequences

		$string =  preg_replace('/\033\[[0-?9;]*[mK]|(\033\]8;.*?\033\\\)/', '', $string);

		// Return the width of the string

		return (int) mb_strwidth($string);
	}
}
