<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd\traits;

use GdImage;
use mako\pixel\image\Color;

use function imagecolorallocatealpha;
use function round;

/**
 * Operation trait.
 */
trait OperationTrait
{
	/**
	 * Allocates a color in the provided GD image resource.
	 */
	protected function allocateColor(GdImage $imageResource, Color $color): int
	{
		return imagecolorallocatealpha(
			$imageResource,
			$color->red,
			$color->green,
			$color->blue,
			127 - (int) round($color->alpha / 255 * 127),
		);
	}
}
