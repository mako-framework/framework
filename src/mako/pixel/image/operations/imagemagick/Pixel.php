<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\Color;
use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\Point;
use Override;

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
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$width = $imageResource->getImageWidth();
		$height = $imageResource->getImageHeight();

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

		$imageResource->importImagePixels(
			$this->pixel->x,
			$this->pixel->y,
			1,
			1,
			'RGBA',
			Imagick::PIXEL_CHAR,
			[
				$this->color->red,
				$this->color->green,
				$this->color->blue,
				$this->color->alpha,
			]
		);
	}
}
