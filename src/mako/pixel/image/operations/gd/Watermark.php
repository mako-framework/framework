<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\Dimensions;
use mako\pixel\image\Gd;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\traits\NormalizeTrait;
use mako\pixel\image\operations\WatermarkPosition;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagealphablending;
use function imagecolorallocatealpha;
use function imagecolorat;
use function imagecopy;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function min;
use function round;

/**
 * Adds a watermark to the image.
 */
class Watermark implements OperationInterface
{
	use GdTrait;
	use NormalizeTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Gd|string $image,
		protected WatermarkPosition $position = WatermarkPosition::BottomRight,
		protected int $opacity = 100,
		protected int $margin = 0
	) {
		if ($image instanceof Gd === false) {
			$this->image = new Gd($image);
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$watermark = $this->image->getImageResource();

		$watermarkWidth = imagesx($watermark);
		$watermarkHeight = imagesy($watermark);

		if ($this->opacity < 100) {
			$watermark = $this->createTruecolorCopyIfNeeded($watermark, $watermarkWidth, $watermarkHeight, $copyCreated);

			if ($copyCreated === false) {
				imagealphablending($watermark, false);
			}

			$opacityAlpha = 127 - round($this->normalizePercent($this->opacity) * 127 / 100);

			for ($x = 0; $x < $watermarkWidth; $x++) {
				for ($y = 0; $y < $watermarkHeight; $y++) {
					$rgb = imagecolorat($watermark, $x, $y);

					imagesetpixel($watermark, $x, $y, imagecolorallocatealpha(
						$watermark,
						($rgb >> 16) & 0xFF,                            // R
						($rgb >> 8) & 0xFF,                             // G
						$rgb & 0xFF,                                    // B
						min(127, (($rgb >> 24) & 0x7F) + $opacityAlpha) // A
					));
				}
			}
		}

		$point = $this->position->resolvePosition(
			new Dimensions(imagesx($imageResource), imagesy($imageResource)),
			new Dimensions($watermarkWidth, $watermarkHeight),
			$this->margin
		);

		imagecopy($imageResource, $watermark, $point->x, $point->y, 0, 0, $watermarkWidth, $watermarkHeight);
	}
}
