<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Colorize as BaseColorize;
use Override;

use function imagealphablending;
use function imagecolorat;
use function imageistruecolor;
use function imagepalettetotruecolor;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function max;
use function min;

/**
 * {@inheritDoc}
 */
class Colorize extends BaseColorize
{
	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if (!imageistruecolor($imageResource)) {
			imagepalettetotruecolor($imageResource);
		}

		// Disable blending so that pixels are replaced instead of blended

		imagealphablending($imageResource, false);

		$r = $this->color->red;
		$g = $this->color->green;
		$b = $this->color->blue;

		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$color = imagecolorat($imageResource, $x, $y);

				imagesetpixel(
					$imageResource,
					$x,
					$y,
					($color & 0x7F000000)                                    // A
					| (max(0, min(255, (($color >> 16) & 0xFF) + $r)) << 16) // R
					| (max(0, min(255, (($color >> 8) & 0xFF) + $g)) << 8)   // G
					| max(0, min(255, ($color & 0xFF) + $b))                 // B
				);
			}
		}
	}
}
