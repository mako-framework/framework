<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Ellipse as EllipseOperation;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagealphablending;
use function imageellipse;
use function imagefilledellipse;
use function imagesetthickness;

/**
 * {@inheritDoc}
 */
class Ellipse extends EllipseOperation
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
			imagefilledellipse(
				$imageResource,
				$this->center->x,
				$this->center->y,
				$this->dimensions->width,
				$this->dimensions->height,
				$this->allocateColor($imageResource, $this->fill)
			);
		}

		if ($this->stroke !== null) {
			imagesetthickness($imageResource, $this->strokeWidth);

			imageellipse(
				$imageResource,
				$this->center->x,
				$this->center->y,
				$this->dimensions->width,
				$this->dimensions->height,
				$this->allocateColor($imageResource, $this->stroke)
			);

			imagesetthickness($imageResource, 1);
		}
	}
}
