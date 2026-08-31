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

		$draw->setFillColor(new ImagickPixel($this->color->toHexaString()));

		$width = $imageResource->getImageWidth();
		$height = $imageResource->getImageHeight();

		$draw->rectangle(0, 0, $width - 1, $this->width - 1);
		$draw->rectangle(0, $height - $this->width, $width - 1, $height - 1);
		$draw->rectangle(0, 0, $this->width - 1, $height - 1);
		$draw->rectangle($width - $this->width, 0, $width - 1, $height - 1);

		$imageResource->drawImage($draw);

		$draw->clear();
		$draw->destroy();
	}
}
