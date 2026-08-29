<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Polygon as BasePolygon;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagealphablending;
use function imagefilledpolygon;
use function imagepolygon;
use function imagesetthickness;

/**
 * {@inheritDoc}
 */
class Polygon extends BasePolygon
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
		$points = [];

		foreach ($this->points as $point) {
			$points[] = $point->x + $this->position->x;
			$points[] = $point->y + $this->position->y;
		}

		imagealphablending($imageResource, true);

		if ($this->fill !== null) {
			imagefilledpolygon(
				$imageResource,
				$points,
				$this->allocateColor($imageResource, $this->fill),
			);
		}

		if ($this->stroke !== null) {
			imagesetthickness($imageResource, $this->strokeWidth);

			imagepolygon(
				$imageResource,
				$points,
				$this->allocateColor($imageResource, $this->stroke),
			);

			imagesetthickness($imageResource, 1);
		}
	}
}
