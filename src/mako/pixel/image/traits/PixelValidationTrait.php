<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\traits;

use mako\pixel\image\exceptions\ImageException;
use mako\pixel\image\operations\Point;

/**
 * Pixel validation trait.
 */
trait PixelValidationTrait
{
	/*
	 * Validates that the pixel is within the image bounds.
	 */
	protected function validatePixel(Point $pixel, int $width, int $height): void
	{
		if (
			$pixel->x < 0 ||
			$pixel->y < 0 ||
			$pixel->x >= $width ||
			$pixel->y >= $height
		) {
			throw new ImageException(sprintf(
				'Pixel coordinates [ %d, %d ] are outside image bounds [ %d x %d ].',
				$pixel->x,
				$pixel->y,
				$width,
				$height,
			));
		}
	}
}
