<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use mako\pixel\image\operations\RoundedRectangle as BaseRoundedRectangle;
use Override;

/**
 * {@inheritDoc}
 */
class RoundedRectangle extends BaseRoundedRectangle
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

			$draw->roundRectangle(
				$this->position->x,
				$this->position->y,
				$this->position->x + $this->dimensions->width,
				$this->position->y + $this->dimensions->height,
				$this->radius,
				$this->radius
			);

			$imageResource->drawImage($draw);
		}
		finally {
			$draw->clear();
			$draw->destroy();
		}
	}
}
