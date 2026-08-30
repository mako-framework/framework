<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Saturation as SaturationOperation;
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
class Saturation extends SaturationOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->level === 0) {
			return;
		}

		if (!imageistruecolor($imageResource)) {
			imagepalettetotruecolor($imageResource);
		}

		// Disable blending so that pixels are replaced instead of blended

        imagealphablending($imageResource, false);

		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$factor = 1 + ($this->level / 100);

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

				$gray = (int) ($r * 0.299 + $g * 0.587 + $b * 0.114);

				imagesetpixel(
					$imageResource,
					$x,
					$y,
					($a << 24)                                                         // A
					| (max(0, min(255, (int) ($gray + ($r - $gray) * $factor))) << 16) // R
					| (max(0, min(255, (int) ($gray + ($g - $gray) * $factor))) << 8)  // G
					| max(0, min(255, (int) ($gray + ($b - $gray) * $factor)))         // B
				);
			}
		}
	}
}
