<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\traits\PixelValidationTrait;
use Override;

/**
 * Draws a pixel on the image at the specified coordinates.
 */
class Pixel implements OperationInterface
{
	use PixelValidationTrait;

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
		$this->validatePixel(
			$this->pixel,
			$imageResource->getImageWidth(),
			$imageResource->getImageHeight()
		);

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
