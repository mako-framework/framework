<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
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
 * Adjusts the image color temperature.
 */
class Temperature implements OperationInterface
{
	use NormalizeTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $level = 0
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

		$level = $this->normalizeLevel($this->level);

		 // Warm: boost red, reduce blue - Cool: the opposite

		$shift = $level * 0.0022;

		$redMultiplier = 1 + $shift;
		$blueMultiplier = 1 - $shift;

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
					($a << 24)                                                              // A
					| (min(255, (int) ((($color >> 16) & 0xFF) * $redMultiplier)) << 16)    // R
					| ($color & 0x0000FF00)                                                 // G
					| min(255, (int) (($color & 0xFF) * $blueMultiplier))                   // B
				);
			}
		}
	}
}
