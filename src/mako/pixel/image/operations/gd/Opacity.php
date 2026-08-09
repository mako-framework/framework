<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagealphablending;
use function imagecolorallocatealpha;
use function imagecolorat;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function min;
use function round;

/**
 * Adjusts the opacity of the image.
 */
class Opacity implements OperationInterface
{
	use GdTrait;
	use NormalizeTrait;

	public function __construct(
		protected int $opacity
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$imageResource = $this->createTruecolorCopyIfNeeded($imageResource, $width, $height, $copyCreated);

		if ($copyCreated === false) {
			imagealphablending($imageResource, false);
		}

		$opacityAlpha = 127 - round($this->normalizePercent($this->opacity) * 127 / 100);

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$color = imagecolorat($imageResource, $x, $y);

				$a = ($color >> 24) & 0x7F;

				if ($a === 127) {
					continue;
				}

				imagesetpixel(
					$imageResource,
					$x,
					$y,
					imagecolorallocatealpha(
						$imageResource,
						($color >> 16) & 0xFF,       // R
						($color >> 8) & 0xFF,        // G
						$color & 0xFF,               // B
						min(127, $a + $opacityAlpha) // A
					)
				);
			}
		}
	}
}
