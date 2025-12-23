<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\ImageInterface;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\WatermarkPosition;
use Override;

use function imagealphablending;
use function imagecolorallocatealpha;
use function imagecolorat;
use function imagecopy;
use function imagesavealpha;
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
	/**
	 * Constructor.
	 */
	public function __construct(
		protected ImageInterface $image,
		protected WatermarkPosition $position = WatermarkPosition::BOTTOM_RIGHT,
		protected int $opacity = 100,
		protected int $margin = 0
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
		$watermark = $this->image->getImageResource();

		imagealphablending($watermark, false);
		imagesavealpha($watermark, true);

		$watermarkWidth = imagesx($watermark);
		$watermarkHeight = imagesy($watermark);

		if ($this->opacity < 100) {
			$opacityAlpha = 127 - round($this->opacity * 127 / 100);

			for ($x = 0; $x < $watermarkWidth; $x++) {
				for ($y = 0; $y < $watermarkHeight; $y++) {
					$rgb = imagecolorat($watermark, $x, $y);

					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
					$a = ($rgb >> 24) & 0x7F;

					$newAlpha = min(127, $a + $opacityAlpha);

					$color = imagecolorallocatealpha($watermark, $r, $g, $b, $newAlpha);

					imagesetpixel($watermark, $x, $y, $color);
				}
			}
		}

		switch ($this->position) {
			case WatermarkPosition::TOP_RIGHT:
				$x = imagesx($imageResource) - $watermarkWidth - $this->margin;
				$y = 0 + $this->margin;
				break;
			case WatermarkPosition::BOTTOM_LEFT:
				$x = 0 + $this->margin;
				$y = imagesy($imageResource) - $watermarkHeight - $this->margin;
				break;
			case WatermarkPosition::BOTTOM_RIGHT:
				$x = imagesx($imageResource) - $watermarkWidth - $this->margin;
				$y = imagesy($imageResource) - $watermarkHeight - $this->margin;
				break;
			case WatermarkPosition::CENTER:
				$x = (imagesx($imageResource) - $watermarkWidth) / 2;
				$y = (imagesy($imageResource) - $watermarkHeight) / 2;
				break;
			default:
				$x = 0 + $this->margin;
				$y = 0 + $this->margin;
		}

		imagealphablending($imageResource, true);

		imagesavealpha($imageResource, true);

		imagecopy($imageResource, $watermark, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight);
	}
}
