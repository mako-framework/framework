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
 * @author Frederic G. Østby
 */
trait HelperTrait
{
	/**
	 * Returns the width of the string without formatting.
	 *
	 * @param  string $string String to strip
	 * @return int
	 */
	protected function stringWidthWithoutFormatting(string $string): int
	{
		$formatter = $this->output->getFormatter();

		return (int) mb_strwidth($formatter !== null ? $formatter->stripTags($string) : $string);
	}
}
