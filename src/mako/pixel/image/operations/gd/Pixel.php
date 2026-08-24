<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\Color;
use mako\pixel\image\geometry\Point;
use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\traits\GdTrait;
use mako\pixel\image\traits\PixelValidationTrait;
use Override;

use function imagesetpixel;
use function imagesx;
use function imagesy;

/**
 * Draws a pixel on the image at the specified coordinates.
 */
class Pixel implements OperationInterface
{
	use GdTrait;
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
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$this->validatePixel(
			$this->pixel,
			imagesx($imageResource),
			imagesy($imageResource)
		);

		imagesetpixel(
			$imageResource,
			$this->pixel->x,
			$this->pixel->y,
			$this->allocateColor($imageResource, $this->color)
		);
	}
}
