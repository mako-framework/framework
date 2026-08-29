<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use mako\pixel\image\operations\Polygon as BasePolygon;
use Override;

/**
 * {@inheritDoc}
 */
class Polygon extends BasePolygon
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
			$draw->setFillColor(new ImagickPixel($this->fill?->toHexaString() ?? 'transparent'));

			if ($this->stroke !== null) {
				$draw->setStrokeColor(new ImagickPixel($this->stroke->toHexaString()));

				$draw->setStrokeWidth($this->strokeWidth);
			}

			$points = [];

			foreach ($this->points as $point) {
				$points[] = [
					'x' => $point->x + $this->position->x,
					'y' => $point->y + $this->position->y,
				];
			}

			$draw->polygon($points);

			$imageResource->drawImage($draw);
		}
		finally {
			$draw->clear();
			$draw->destroy();
		}
	}
}
