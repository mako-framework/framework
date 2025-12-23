<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

use function imagecolorallocatealpha;
use function imagecolorat;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function max;
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
	 * @param \GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource, string $imagePath): void
	{
		if ($this->level === 0) {
			return;
		}

		$width = imagesx($imageResource);
		$height = imagesy($imageResource);

		$level = $this->normalizeLevel($this->level);

		$tempFactor = $level / 200;

		if ($level > 0) {
			$redMultiplier = 1.3 + $tempFactor;
			$blueMultiplier = 1.2 - $tempFactor;
		}
		else {
			$redMultiplier = 1.22 + $tempFactor;
			$blueMultiplier = 0.75 - $tempFactor;
		}

		for ($y = 0; $y < $height; $y++) {
			for ($x = 0; $x < $width; $x++) {
				$rgb = imagecolorat($imageResource, $x, $y);

				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				$a = ($rgb >> 24) & 0x7F;

				$newR = min(255, max(0, (int) ($r * $redMultiplier)));
				$newB = min(255, max(0, (int) ($b * $blueMultiplier)));

				$newColor = imagecolorallocatealpha($imageResource, $newR, $g, $newB, $a);

				imagesetpixel($imageResource, $x, $y, $newColor);
			}
		}
	}
}
