<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use mako\pixel\image\operations\Pixel as PixelOperation;
use mako\pixel\image\traits\PixelValidationTrait;
use Override;

/**
 * {@inheritDoc}
 */
class Pixel extends PixelOperation
{
	use PixelValidationTrait;

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
