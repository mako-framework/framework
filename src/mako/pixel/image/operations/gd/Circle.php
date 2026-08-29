<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Circle as BaseCircle;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagealphablending;
use function imageellipse;
use function imagefilledellipse;
use function imagesetthickness;

/**
 * {@inheritDoc}
 */
class Circle extends BaseCircle
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

		$diameter = $this->radius * 2;

		if ($this->fill !== null) {
			imagefilledellipse(
				$imageResource,
				$this->center->x,
				$this->center->y,
				$diameter,
				$diameter,
				$this->allocateColor($imageResource, $this->fill)
			);
		}

		if ($this->stroke !== null) {
			imagesetthickness($imageResource, $this->strokeWidth);

			imageellipse(
				$imageResource,
				$this->center->x,
				$this->center->y,
				$diameter,
				$diameter,
				$this->allocateColor($imageResource, $this->stroke)
			);

			imagesetthickness($imageResource, 1);
		}
	}
}
