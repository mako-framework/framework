<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use mako\pixel\image\operations\Polyline as PolylineOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Polyline extends PolylineOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$draw = new ImagickDraw;

		try {
			$draw->setFillColor('transparent');

			$draw->setStrokeColor(new ImagickPixel($this->stroke->toHexaString()));

			$draw->setStrokeWidth($this->strokeWidth);

			$points = [];

			foreach ($this->points as $point) {
				$points[] = [
					'x' => $point->x + $this->position->x,
					'y' => $point->y + $this->position->y,
				];
			}

			$draw->polyline($points);

			$imageResource->drawImage($draw);
		}
		finally {
			$draw->clear();
			$draw->destroy();
		}
	}
}
