<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Pixel as PixelOperation;
use mako\pixel\image\traits\GdTrait;
use mako\pixel\image\traits\PixelValidationTrait;
use Override;

use function imagesetpixel;
use function imagesx;
use function imagesy;

/**
 * {@inheritDoc}
 */
class Pixel extends PixelOperation
{
	use GdTrait;
	use PixelValidationTrait;

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
