<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Rectangle as RectangleOperation;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagealphablending;
use function imagefilledrectangle;
use function imagerectangle;
use function imagesetthickness;

/**
 * {@inheritDoc}
 */
class Rectangle extends RectangleOperation
{
	use GdTrait;

	/**
	 * {@inheritDoc}
	 *
	 * @param GdImage &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		imagealphablending($imageResource, true);

		if ($this->fill !== null) {
			imagefilledrectangle(
				$imageResource,
				$this->position->x,
				$this->position->y,
				$this->position->x + $this->dimensions->width,
				$this->position->y + $this->dimensions->height,
				$this->allocateColor($imageResource, $this->fill)
			);
		}

		if ($this->stroke !== null) {
			imagesetthickness($imageResource, $this->strokeWidth);

			imagerectangle(
				$imageResource,
				$this->position->x,
				$this->position->y,
				$this->position->x + $this->dimensions->width,
				$this->position->y + $this->dimensions->height,
				$this->allocateColor($imageResource, $this->stroke)
			);

			imagesetthickness($imageResource, 1);
		}
	}
}
