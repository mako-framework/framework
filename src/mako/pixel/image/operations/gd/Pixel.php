<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\Color;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\Point;
use Override;

use function imagecolorallocatealpha;
use function imagesetpixel;
use function imagesx;
use function imagesy;
use function round;
use function sprintf;

/**
 * Draws a pixel on the image at the specified coordinates.
 */
class Pixel implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Point $pixel,
		protected Color $color
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

		if (
			$this->pixel->x < 0 ||
			$this->pixel->y < 0 ||
			$this->pixel->x >= $width ||
			$this->pixel->y >= $height
		) {
			throw new ImageException(sprintf(
				'Pixel coordinates [ %d, %d ] are outside image bounds [ %d x %d ].',
				$this->pixel->x,
				$this->pixel->y,
				$width,
				$height,
			));
		}

		imagesetpixel(
			$imageResource,
			$this->pixel->x,
			$this->pixel->y,
			imagecolorallocatealpha(
				$imageResource,
				$this->color->red,
				$this->color->green,
				$this->color->blue,
				127 - (int) round($this->color->alpha / 255 * 127),
			)
		);
	}
}
