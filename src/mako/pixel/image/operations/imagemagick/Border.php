<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations\imagemagick;

use ImagickDraw;
use ImagickPixel;
use mako\pixel\image\Color;
use mako\pixel\image\operations\OperationInterface;
use Override;

/**
 * Adds a border to the image.
 */
class Border implements OperationInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Color $color = new Color(0, 0, 0),
		protected int $width = 5
	) {
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param \Imagick &$imageResource
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		$draw = new ImagickDraw;

		$draw->setStrokeColor(new ImagickPixel($this->color->toRgbaString()));
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
