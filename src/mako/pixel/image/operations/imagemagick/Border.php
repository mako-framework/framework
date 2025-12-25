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
		protected int $size = 5
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
		$draw->setStrokeWidth($this->size);
		$draw->setFillOpacity(0);
		$draw->setStrokeAntialias(true);

		$width = $imageResource->getImageWidth();
		$height = $imageResource->getImageHeight();

		$draw->rectangle(
			$this->size / 2,
			$this->size / 2,
			$width - $this->size / 2,
			$height - $this->size / 2
		);

		$imageResource->drawImage($draw);

		$draw->clear();
		$draw->destroy();
	}
}
