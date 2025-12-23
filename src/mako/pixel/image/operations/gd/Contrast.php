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
 * Adjusts the image contrast.
 */
class Contrast implements OperationInterface
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

		$factor = 1 + (((100 + $level) / 100) - 1) * 0.8;

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$rgb = imagecolorat($imageResource, $x, $y);

				imagesetpixel($imageResource, $x, $y, imagecolorallocate(
					$imageResource,
					max(0, min(255, (((($rgb >> 16) & 0xFF) / 255 - 0.5) * $factor + 0.5) * 255)), // R
					max(0, min(255, (((($rgb >> 8) & 0xFF) / 255 - 0.5) * $factor + 0.5) * 255)),  // G
					max(0, min(255, ((($rgb & 0xFF) / 255 - 0.5) * $factor + 0.5) * 255))          // B
				));
			}
		}
	}
}
