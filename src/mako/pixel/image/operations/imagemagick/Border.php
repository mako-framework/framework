<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use mako\pixel\image\operations\Border as BorderOperation;
use Override;

/**
 * {@inheritDoc}
 */
class Border extends BorderOperation
{
	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		if ($this->width === 0) {
			return;
		}

		$draw = new ImagickDraw;

		$draw->setStrokeColor(new ImagickPixel($this->color->toHexaString()));
		$draw->setStrokeWidth($this->width);
		$draw->setFillOpacity(0);
		$draw->setStrokeAntialias(true);

		$width = $imageResource->getImageWidth();
		$height = $imageResource->getImageHeight();

		$draw->rectangle(
			$this->width / 2,
			$this->width / 2,
			$width - $this->width / 2,
			$height - $this->width / 2
		);

		$imageResource->drawImage($draw);

		$draw->clear();
		$draw->destroy();
	}
}
