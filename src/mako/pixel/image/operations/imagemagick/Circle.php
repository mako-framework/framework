<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use mako\pixel\image\operations\Circle as BaseCircle;
use Override;

/**
 * {@inheritDoc}
 */
class Circle extends BaseCircle
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

			$draw->circle(
				$this->center->x,
				$this->center->y,
				$this->center->x,
				$this->center->y + $this->radius
			);

			$imageResource->drawImage($draw);
		}
		finally {
			$draw->clear();
			$draw->destroy();
		}
	}
}
