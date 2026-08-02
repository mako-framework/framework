<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\gd\traits;

use function round;

/**
 * Inspector trait.
 */
trait InspectorTrait
{
	/**
	 * Converts a GD color to a RGBA array.
	 *
	 * @return array{0:int, 1:int, 2:int, 3:int}
	 */
	protected function convertColorToRgba(int $color): array
	{
		$r = ($color >> 16) & 0xFF;
		$g = ($color >> 8) & 0xFF;
		$b = $color & 0xFF;
		$a = (int) round((127 - (($color & 0x7F000000) >> 24)) / 127 * 255);

		return [$r, $g, $b, $a];
	}
}
