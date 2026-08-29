<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\gd;

use GdImage;
use mako\pixel\image\operations\Polyline as BasePolyline;
use mako\pixel\image\traits\GdTrait;
use Override;

use function imagealphablending;
use function imageline;
use function imagesetthickness;

/**
 * {@inheritDoc}
 */
class Polyline extends BasePolyline
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

		imagesetthickness($imageResource, $this->strokeWidth);

		$color = $this->allocateColor($imageResource, $this->stroke);

		$points = $this->points->getPoints();

		foreach ($points as $key => $point) {
			if (!isset($points[$key + 1])) {
				break;
			}

			$next = $points[$key + 1];

			imageline(
				$imageResource,
				$point->x + $this->position->x,
				$point->y +  $this->position->y,
				$next->x +  $this->position->x,
				$next->y +  $this->position->y,
				$color
			);
		}

		imagesetthickness($imageResource, 1);
	}
}
