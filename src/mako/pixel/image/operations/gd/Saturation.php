<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use Override;

use function imagecolorallocate;
use function imagecolorat;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function max;
use function min;

/**
 * Adjusts the color saturation.
 */
class Saturation implements OperationInterface
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

		$factor = 1 + ($level / 100);

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$rgb = imagecolorat($imageResource, $x, $y);

				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;

				$gray = (int) ($r * 0.299 + $g * 0.587 + $b * 0.114);

				$r = max(0, min(255, ($gray + ($r - $gray) * $factor)));
				$g = max(0, min(255, ($gray + ($g - $gray) * $factor)));
				$b = max(0, min(255, ($gray + ($b - $gray) * $factor)));

				imagesetpixel($imageResource, $x, $y, imagecolorallocate($imageResource, $r, $g, $b));
			}
		}
	}
}
