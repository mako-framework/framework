<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\ImageMagick;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\WatermarkPosition;
use Override;

/**
 * Adds a watermark to the image.
 */
class Watermark implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected ImageMagick|string $image,
		protected WatermarkPosition $position = WatermarkPosition::BottomRight,
		protected int $opacity = 100,
		protected int $margin = 0
	) {
		if ($image instanceof ImageMagick === false) {
			$this->image = new ImageMagick($image);
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$watermark = $this->image->getImageResource();

		$watermarkWidth = $watermark->getImageWidth();
		$watermarkHeight = $watermark->getImageHeight();

		if ($this->opacity < 100) {
			$watermark->evaluateImage(Imagick::EVALUATE_MULTIPLY, ($this->opacity / 100), Imagick::CHANNEL_ALPHA);
		}

		switch ($this->position) {
			case WatermarkPosition::TopRight:
				$x = $imageResource->getImageWidth() - $watermarkWidth - $this->margin;
				$y = 0 + $this->margin;
				break;
			case WatermarkPosition::BottomLeft:
				$x = 0 + $this->margin;
				$y = $imageResource->getImageHeight() - $watermarkHeight - $this->margin;
				break;
			case WatermarkPosition::BottomRight:
				$x = $imageResource->getImageWidth() - $watermarkWidth - $this->margin;
				$y = $imageResource->getImageHeight() - $watermarkHeight - $this->margin;
				break;
			case WatermarkPosition::Center:
				$x = ($imageResource->getImageWidth() - $watermarkWidth) / 2;
				$y = ($imageResource->getImageHeight() - $watermarkHeight) / 2;
				break;
			default:
				$x = 0 + $this->margin;
				$y = 0 + $this->margin;
		}

		$imageResource->compositeImage($watermark, Imagick::COMPOSITE_OVER, $x, $y);
	}
}
