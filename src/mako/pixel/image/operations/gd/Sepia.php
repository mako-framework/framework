<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\OperationInterface;
use Override;

use function imagealphablending;
use function imagecolorat;
use function imageistruecolor;
use function imagepalettetotruecolor;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function min;

/**
 * Turns the image into sepia.
 */
class Sepia implements OperationInterface
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

		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$color = imagecolorat($imageResource, $x, $y);

				$a = ($color >> 24) & 0x7F;

				if ($a === 127) {
					continue;
				}

				$r = ($color >> 16) & 0xFF;
				$g = ($color >> 8) & 0xFF;
				$b = $color & 0xFF;

				imagesetpixel(
					$imageResource,
					$x,
					$y,
					($a << 24)                                                              // A
					| ((int) min(255, ($r * 0.393 + $g * 0.769 + $b * 0.189) * 0.85) << 16) // R
					| ((int) min(255, ($r * 0.349 + $g * 0.686 + $b * 0.168) * 0.85) << 8)  // G
					| (int) min(255, ($r * 0.272 + $g * 0.534 + $b * 0.131) * 0.85)         // B
				);
			}
		}
	}
}
