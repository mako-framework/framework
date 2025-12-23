<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use mako\pixel\image\Gd;
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
		protected Gd|string $image,
		protected WatermarkPosition $position = WatermarkPosition::BOTTOM_RIGHT,
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
